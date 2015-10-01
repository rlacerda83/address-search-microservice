<?php

namespace App\Services\AddressSearch;

use App\Helpers\Objects;
use App\Services\AddressSearch\PostOfficeBrazil\PostOffice;
use App\Services\AddressSearch\PostOfficeBrazil\ViaCep;

class PostOfficeBrazil extends AddressSearchAbstract
{
    const CAHCE_VIACEP_KEY = 'viacep_';
    const CAHCE_POSTOFFICE_KEY = 'postoffice_';
    const CAHCE_LOCALDATABASE_KEY = 'localdatabase_';

    /**
     * @var
     */
    protected $response;

    public function validPostalCode()
    {
        $value = $this->getPostalCode();
        $value = str_replace('.', '', $value);
        $value = str_replace('-', '', $value);
        if (mb_strlen($value) === 8 && preg_match('/^(\d){8}$/', $value)) {
            $this->setPostalCode($value);

            return;
        }

        $this->setPostalCode(false);
    }

    public function search()
    {
        $this->validPostalCode();
        if (!$this->getPostalCode()) {
            throw new \Exception("Invalid postal code '{$this->getPostalCode()}'");
        }

        // search in database first
        $response = $this->searchDatabase();

        //if not found in database search in post office site
        if (!$response) {
            $response = $this->getByPostOfficeMobile();
            if (!strlen($response->getError())) {
                $this->updateDatabase($response);
            }
        }

        if ($response->getError()) {
            $response = $this->getByViaCep();
            if (!strlen($response->getError())) {
                $this->updateDatabase($response);
            }
        }

        return Objects::toArray($response);
    }

    private function getByViaCep()
    {
        return ViaCep::getAddress($this->getPostalCode());
    }

    private function getByPostOfficeMobile()
    {
        return PostOffice::getAddress($this->getPostalCode());
    }
}
