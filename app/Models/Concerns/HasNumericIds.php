<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;
use MongoDB\Operation\FindOneAndUpdate;

trait HasNumericIds
{
    protected static function bootHasNumericIds(): void
    {
        static::creating(function ($model): void {
            if (filled($model->getKey())) {
                return;
            }

            $model->setAttribute($model->getKeyName(), static::nextNumericIdentifier());
        });
    }

    protected static function nextNumericIdentifier(): int
    {
        $collection = DB::connection('mongodb')->getCollection('counters');

        $result = $collection->findOneAndUpdate(
            ['_id' => static::counterKey()],
            ['$inc' => ['seq' => 1]],
            [
                'upsert' => true,
                'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER,
            ],
        );

        return (int) ($result['seq'] ?? 1);
    }

    protected static function counterKey(): string
    {
        return (new static())->getTable();
    }
}
