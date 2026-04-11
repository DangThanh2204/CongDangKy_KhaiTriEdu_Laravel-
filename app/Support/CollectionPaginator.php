<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CollectionPaginator
{
    public static function paginate(iterable $items, int $perPage, int $page, array $options = []): LengthAwarePaginator
    {
        $collection = $items instanceof Collection ? $items->values() : collect($items)->values();
        $page = max($page, 1);
        $perPage = max($perPage, 1);
        $total = $collection->count();

        return new LengthAwarePaginator(
            $collection->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            $options,
        );
    }
}
