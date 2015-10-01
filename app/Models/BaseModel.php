<?php

namespace App\Models;

use Jenssegers\Mongodb\Model as Eloquent;

class BaseModel extends Eloquent
{
    public static function getTableName()
    {
        return with(new static())->getTable();
    }
}
