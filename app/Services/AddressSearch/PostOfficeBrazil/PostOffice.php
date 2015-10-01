<?php

namespace App\Services\AddressSearch\PostOfficeBrazil;

use App\Services\AddressSearch\Response;
use Cache;
use GuzzleHttp\Client;
use Yangqi\Htmldom\Htmldom;

class PostOffice
{
    const CAHCE_POSTOFFICE_KEY = 'postoffice_';
    const CACHE_MINUTES = 60;
    const URL_POSTOFFICE_MOBILE = 'http://m.correios.com.br/movel/buscaCepConfirma.do';

    public static function getAddress($postalCode)
    {
        $params = [
            'cepEntrada' => $postalCode,
            'tipoCep'    => '',
            'cepTemp'    => '',
            'metodo'     => 'buscarCep',
        ];

        $cacheKey = md5(self::CAHCE_POSTOFFICE_KEY.$postalCode);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey, new Response(true));
        }

        $client = new Client();
        $response = $client->request('POST', self::URL_POSTOFFICE_MOBILE, [
            'form_params' => $params,
        ]);

        if ($response->getStatusCode() === 200) {
            $result = $response->getBody();
            $html = new Htmldom($result);
            $ret = $html->find('div[class=caixacampobranco]');

            $responsePage = $html->find('span.respostadestaque');

            $arrayResponse = [];
            $count = 0;
            foreach ($responsePage as $content) {
                $key = self::cleanString($html->find('span.resposta', $count)->plaintext);
                $arrayResponse[$key] = trim($content->plaintext);
                $count++;
            }

            $response = self::parseResponse($arrayResponse);
            Cache::put($cacheKey, $response, self::CACHE_MINUTES);
        } else {
            return new Response(true);
        }

        return $response;
    }

    private static function cleanString($string)
    {
        $value = preg_replace('/(\s+|\:)/', '', $string);

        return str_replace('/', '-', $value);
    }

    private static function parseResponse($result)
    {
        $city = null;
        $state = null;
        if (isset($result['Localidade-UF'])) {
            $aux = explode('/', $result['Localidade-UF']);
            $city = trim(preg_replace('/\t+/', '', $aux[0]));
            $state = trim(preg_replace('/\t+/', '', $aux[1]));
        }

        $response = new Response();
        $response->setAddress(isset($result['Logradouro']) ? $result['Logradouro'] : '')
            ->setNeighborhood(isset($result['Bairro']) ? $result['Bairro'] : '')
            ->setCity($city)
            ->setState($state)
            ->setPostalCode(isset($result['CEP']) ? $result['CEP'] : '')
            ->setError(count($result) ? '' : 'Postal code not found');

        return $response;
    }
}
