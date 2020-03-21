<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry;

use ArrayIterator;
use Bungle\Framework\Inquiry\ArrayQueryBuilder;
use Bungle\Framework\Inquiry\DBProviderInterface;
use Bungle\Framework\Inquiry\Inquiry;
use Bungle\Framework\Inquiry\PagedData;
use Bungle\Framework\Inquiry\Query;
use Bungle\Framework\Inquiry\QueryParams;
use Bungle\Framework\Inquiry\StepContext;
use PHPUnit\Framework\TestCase;

final class InquiryTest extends TestCase
{
    // Returns [Inquiry, DBProviderMock, EmptyQueryParams, EmptyQuery]
    private function createObjects(): array
    {
        $db = $this->createMock(DBProviderInterface::class);
        $q = new Query();
        $q->offset = 0;
        $q->count = -1;
        $q->docClass = Order::class;
        $q->fields = [];
        $q->conditions = [];

        $params = new QueryParams(Order::class, 0, OrderQBE::class);
        $inquiry = new Inquiry($db);

        return [
            $inquiry,
            $this->db = $db,
            $params,
            $q,
        ];
    }

    private function mockSearch(Query $q, iterable $returns): void
    {
        $this
            ->db
            ->expects($this->once())
            ->method('search')
            ->with($this->equalTo($q))
            ->willReturn($returns)
        ;
    }

    public function mockCount(Query $q, int $returns): void
    {
        $this
            ->db
            ->expects($this->once())
            ->method('count')
            ->with($this->equalTo($q))
            ->willReturn($returns)
        ;
    }

    public function testSearchEmpty(): void
    {
        list($inquiry, , $params, $q) = $this->createObjects();
        $this->mockSearch($q, []);

        $qb = new ArrayQueryBuilder([]);
        self::assertEmpty($inquiry->search($qb, $params));
    }

    public function testSearchSteps(): void
    {
        list($inquiry, , $params, $q) = $this->createObjects();

        $qb = new ArrayQueryBuilder([
            function (StepContext $ctx): void {
                self::assertFalse($ctx->isBuildForCount());
                $ctx->query->offset = 10;
                $ctx->set('foo', 'bar');
            },
            fn ($ctx) => $ctx->query->fields[] = $ctx->get('foo'),
        ]);

        $q->offset = 10;
        $q->fields = ['bar'];
        $this->mockSearch($q, []);
        self::assertEmpty($inquiry->search($qb, $params));
    }

    public function testPagedEmpty(): void
    {
        list($inquiry, , $params, $q) = $this->createObjects();
        $this->mockCount($q, 0);

        $qb = new ArrayQueryBuilder([]);
        $exp = new PagedData([], 0);

        self::assertEquals($exp, $inquiry->paged($qb, $params));
    }

    public function testPaged(): void
    {
        list($inquiry, , $params, $q) = $this->createObjects();

        $qCount = clone $q;
        $qSearch = clone $q;

        $qb = new ArrayQueryBuilder([
            fn ($ctx) => $ctx->query->offset = $ctx->isBuildForCount() ? 3 : 4,
        ]);

        $qCount->offset = 3;
        $qSearch->offset = 4;
        $this->mockCount($qCount, 2);
        $this->mockSearch($qSearch, [1, 2, 3]);
        self::assertEquals(new PagedData([1, 2, 3], 2), $inquiry->paged($qb, $params));
    }

    public function testPagedIterator(): void
    {
        list($inquiry, , $params, $q) = $this->createObjects();

        $qCount = clone $q;
        $qSearch = clone $q;

        $qb = new ArrayQueryBuilder([
            fn ($ctx) => $ctx->query->offset = $ctx->isBuildForCount() ? 3 : 4,
        ]);

        $qCount->offset = 3;
        $qSearch->offset = 4;
        $this->mockCount($qCount, 2);
        $this->mockSearch($qSearch, new ArrayIterator([1, 2, 3]));
        self::assertEquals(new PagedData([1, 2, 3], 2), $inquiry->paged($qb, $params));
    }
}
