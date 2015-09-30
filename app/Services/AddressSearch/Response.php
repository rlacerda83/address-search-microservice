<?php

namespace App\Services\AddressSearch;

class Response
{
    protected $address = '';

    protected $neighborhood = '';

    protected $city = '';

    protected $state = '';

    protected $postalCode = '';

    protected $error = '';

    public function __construct($error = false)
    {
        if ($error === true) {
            $this->setError($error);
        }
    }

    public function setAddress($address)
    {
        $this->address = (string) $address;

        return $this;
    }

    public function setNeighborhood($neighborhood)
    {
        $this->neighborhood = (string) $neighborhood;

        return $this;
    }

    public function setCity($city)
    {
        $this->city = (string) $city;

        return $this;
    }

    public function setState($state)
    {
        $this->state = (string) $state;

        return $this;
    }

    public function setPostalCode($postalCode)
    {
        $this->postalCode = (string) $postalCode;

        return $this;
    }

    public function setError($error)
    {
        $this->error = (string) $error;

        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getNeighborhood()
    {
        return $this->neighborhood;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function getError()
    {
        return $this->error;
    }
}
