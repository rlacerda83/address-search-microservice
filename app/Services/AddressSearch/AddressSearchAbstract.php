<?php

namespace App\Services\AddressSearch;

use App\Models\ServiceSearch;
use App\Models\Address;
use App\Repositories\Mongo\AddressRepository;
use App\Services\AddressSearch\Response;

abstract class AddressSearchAbstract
{

    /**
     * @var ServiceSearch
     */
    protected $serviceSearch;

    /**
     * @var AddressRepository
     */
    private $addressRepository;

    /**
     * @var string
     */
    protected $postalCode;

    /**
     * @var integer
     */
    protected $cacheForMinutes = 60;

    protected $addressOutSync = false;

    /**
     * @param ServiceSearch $addressSearch
     */
    public function __construct(ServiceSearch $serviceSearch, AddressRepository $addressRepository)
    {
        $this->serviceSearch = $serviceSearch;
        $this->addressRepository = $addressRepository;
    }

    public function getModel()
    {
        return $this->serviceSearch;
    }

    public function setPostalCode($value)
    {
        $this->postalCode = (string) $value;

        return $this;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function searchDatabase()
    {
        $result = $this->addressRepository->findByCountryAndPostalCode($this->getModel()->country_code, $this->getPostalCode());

        if (! $result) {
            return false;
        }

        $addressNeedSync = $this->addressNeedSync($result);
        if (! $addressNeedSync) {
            return false;
        }

        return $this->parseResponse($result);
    }

    private function addressNeedSync($result)
    {
        $created = new \Carbon\Carbon($result->updated_at);
        $now = \Carbon\Carbon::now();
        $difference = $created->diffInMonths($now);

        if ($difference >= 6) {
            $this->addressOutSync = $result;
            return false;
        }

        return true;
    }

    public function updateDatabase(Response $response) 
    {   
        $arrayUpdate = [
            'address1' => $response->getAddress(),
            'neighborhood' => $response->getNeighborhood(),
            'city' => $response->getCity(),
            'state' => $response->getState(),
            'zip' => $response->getPostalCode(),
            'country_code' => $this->getModel()->country_code
        ];

        $validation = $this->addressRepository->validateRequest(null, $arrayUpdate);
        if (is_array($validation)) {
            return false;
        }

        if ($this->addressOutSync) {
            $this->addressRepository->update($arrayUpdate, $this->addressOutSync);
            
        } else {
            $this->addressRepository->create($arrayUpdate);
        }    
    }

    private function parseResponse($result)
    {
        $response = new Response();
        $response->setAddress(isset($result['address1']) ? $result['address1'] : '')
            ->setNeighborhood(isset($result['neighborhood']) ? $result['neighborhood'] : '')
            ->setCity(isset($result['city']) ? $result['city'] : '')
            ->setState(isset($result['state']) ? $result['state'] : '')
            ->setPostalCode(isset($result['zip']) ? $result['zip'] : '')
            ->setError('');

        return $response;
    }

    abstract public function search();

    abstract public function validPostalCode();

}
