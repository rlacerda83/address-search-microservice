<?php

namespace App\Models;

use Elocache\Observers\BaseObserver;

class ServiceSearch extends BaseModel
{
    protected $collection = 'search_services';
    protected $connection = 'mongodb';

    protected $fillable = ['name', 'country_code', 'model_reference', 'status'];

    public static function boot()
    {
        parent::boot();

        Self::observe(new BaseObserver());
    }
}
