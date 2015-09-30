<?php

namespace App\Services\AddressSearch\PostOfficeBrazil;

use GuzzleHttp\Client;
use App\Services\AddressSearch\Response;
use Cache;

class ViaCep
{
	const CAHCE_VIACEP_KEY = 'viacep_';
    const CACHE_MINUTES = 60;
    const URL_VIACEP = 'http://viacep.com.br/ws/[cep]/json/';

    public static function getAddress($postalCode)
    {
    	$cacheKey = md5(self::CAHCE_VIACEP_KEY.$postalCode);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey, new Response(true));
        }

        $url = str_replace('[cep]', $postalCode, self::URL_VIACEP);
        $client = new Client();

        $responseGet = $client->get($url);
        if ($responseGet->getStatusCode() === 200) {
            $result = json_decode($responseGet->getBody());
            $response = self::parseResponse($result);
            Cache::put($cacheKey, $response, self::CACHE_MINUTES);
        } else {
            return new Response(true);
        }

        return $response;
    }

    private static function parseResponse($result)
    {
        $response = new Response();
        $response->setAddress(isset($result->logradouro) ? $result->logradouro : '')
            ->setNeighborhood(isset($result->bairro) ? $result->bairro : '')
            ->setCity(isset($result->localidade) ? $result->localidade : '')
            ->setState(isset($result->uf) ? $result->uf : '')
            ->setPostalCode(isset($result->cep) ? $result->cep : '')
            ->setError(isset($result->erro) ? $result->erro : '');

        return $response;
    }
}
