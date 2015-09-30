<?php

namespace App\Models;

class Country extends BaseModel
{
	protected $collection = 'countries';
	protected $connection = 'mongodb';

    protected $fillable = ['code', 'name'];
}
