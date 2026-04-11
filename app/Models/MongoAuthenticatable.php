<?php

namespace App\Models;

use App\Models\Concerns\HasNumericIds;
use MongoDB\Laravel\Auth\User as BaseAuthenticatable;

abstract class MongoAuthenticatable extends BaseAuthenticatable
{
    use HasNumericIds;

    public $incrementing = false;

    protected $connection = 'mongodb';

    protected $primaryKey = 'id';

    protected $keyType = 'int';
}
