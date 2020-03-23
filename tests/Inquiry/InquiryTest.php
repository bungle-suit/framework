<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry;

use ArrayIterator;
use Bungle\Framework\Inquiry\ArrayQueryBuilder;
use Bungle\Framework\Inquiry\Inquiry;
use Bungle\Framework\Inquiry\PagedData;
use Bungle\Framework\Inquiry\QueryParams;
use Bungle\Framework\Inquiry\StepContext;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class InquiryTest extends TestCase
{
    /**
     * @var DocumentManager|MockObject
     */
    private $dm;
    /**
     * @var Query|Stub
     */
    private $query;

    private static function createQueryParams(): QueryParams
    {
        return new QueryParams(Order::class, 0, OrderQBE::class);
    }

    // Returns [Inquiry, DocumentManagerMock, BuilderMock, QueryStub]
    private function createObjects(): array
    {
        $dm = $this->createMock(DocumentManager::class);
        $builder = $this->createMock(Builder::class);
        $dm
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->with(Order::class)
            ->willReturn($builder)
        ;
        $query = $this->createStub(Query::class);
        $builder
            ->expects($this->once())
            ->willReturn($query);
        $inquiry = new Inquiry($dm);

        return [
            $inquiry,
            $this->dm = $dm,
            $builder,
            $this->query = $query,
        ];
    }

    private function mockSearch(iterable $returns): void
    {
        $this->query->method('getIterator')->willReturn($returns);
    }

    public function mockCount(int $returns): void
    {
        $this->query->method('execute')->willReturn($returns);
    }

    public function testSearchEmpty(): void
    {
        $this->markTestSkipped('Failed to mock ODM Query object, we\'ll figured out later.');
        $params = self::createQueryParams();
        list($inquiry) = $this->createObjects();
        $this->mockSearch([]);

        $qb = new ArrayQueryBuilder([]);
        self::assertEmpty($inquiry->search($qb, $params));
    }

    public function testSearchSteps(): void
    {
        $this->markTestSkipped('Failed to mock ODM Query object, we\'ll figured out later.');
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
        $this->markTestSkipped('Failed to mock ODM Query object, we\'ll figured out later.');
        list($inquiry, , $params, $q) = $this->createObjects();
        $this->mockCount($q, 0);

        $qb = new ArrayQueryBuilder([]);
        $exp = new PagedData([], 0);

        self::assertEquals($exp, $inquiry->paged($qb, $params));
    }

    public function testPaged(): void
    {
        $this->markTestSkipped('Failed to mock ODM Query object, we\'ll figured out later.');
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
        $this->markTestSkipped('Failed to mock ODM Query object, we\'ll figured out later.');
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
