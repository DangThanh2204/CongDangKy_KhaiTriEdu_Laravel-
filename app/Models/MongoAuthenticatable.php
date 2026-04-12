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

    protected function asDecimal($value, $decimals)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return parent::asDecimal($value, $decimals);
    }

    protected function fromDecimal($value, $decimals)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return parent::fromDecimal($value, $decimals);
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $field = $field ?: $this->getRouteKeyName();

        if ($this->shouldCastRouteBindingValueToInteger($field, $value)) {
            $value = (int) $value;
        }

        return parent::resolveRouteBindingQuery($query, $value, $field);
    }

    protected function shouldCastRouteBindingValueToInteger(string $field, mixed $value): bool
    {
        return $this->getKeyType() === 'int'
            && $field === $this->getRouteKeyName()
            && is_string($value)
            && preg_match('/^-?\d+$/', $value) === 1;
    }
}
