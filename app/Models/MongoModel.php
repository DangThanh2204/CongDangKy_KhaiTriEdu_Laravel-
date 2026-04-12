<?php

namespace App\Models;

use App\Models\Concerns\HasNumericIds;
use MongoDB\Laravel\Eloquent\Model as BaseModel;

abstract class MongoModel extends BaseModel
{
    use HasNumericIds;

    public $incrementing = false;

    protected $connection = 'mongodb';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected function asDecimal($value, $decimals)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return parent::asDecimal($value, $decimals);
    }
}
