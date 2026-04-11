<?php

namespace Tests\Unit;

use App\Support\CollectionPaginator;
use PHPUnit\Framework\TestCase;

class CollectionPaginatorTest extends TestCase
{
    public function test_it_paginates_an_iterable_with_expected_metadata(): void
    {
        $paginator = CollectionPaginator::paginate([1, 2, 3, 4, 5], 2, 2, [
            'path' => '/courses',
            'pageName' => 'page',
        ]);

        $this->assertSame([3, 4], $paginator->items());
        $this->assertSame(5, $paginator->total());
        $this->assertSame(2, $paginator->currentPage());
        $this->assertSame(3, $paginator->lastPage());
        $this->assertStringContainsString('/courses', $paginator->url(3));
    }
}
