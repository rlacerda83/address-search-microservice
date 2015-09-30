<?php

namespace App\Models;

use Elocache\Observers\BaseObserver;

class Address extends BaseModel
{
	protected $collection = 'address';
	protected $connection = 'mongodb';

    protected $fillable = ['zip', 'address1', 'neighborhood', 'city', 'state', 'country_code', 'geolocation'];

    public static function boot()
    {
        parent::boot();

        Self::observe(new BaseObserver());
    }
}
