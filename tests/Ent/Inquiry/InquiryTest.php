<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Inquiry;

use Bungle\Framework\Ent\Inquiry\ArrayQueryBuilder;
use Bungle\Framework\Ent\Inquiry\Inquiry;
use Bungle\Framework\Ent\Inquiry\PagedData;
use Bungle\Framework\Ent\Inquiry\QueryParams;
use Bungle\Framework\Ent\Inquiry\StepContext;
use Bungle\Framework\Tests\DBTestable;
use MongoDB\Collection;
use PHPUnit\Framework\TestCase;

final class InquiryTest extends TestCase
{
    use DBTestable {
        setup as baseSetup;
    }

    private Collection $coll;
    /** @var Order[] */
    private array $orders;

    protected function setUp(): void
    {
        $this->baseSetup();

        $coll = $this->dm->getDocumentCollection(Order::class);
        $coll->drop();

        $order1 = Order::create('1', 'foo');
        $order2 = Order::create('2', 'foo');
        $order3 = Order::create('3', 'foobar');
        $order4 = Order::create('4', 'blah');
        $this->orders = [$order1, $order2, $order3, $order4];
        $this->dm->persist($order1);
        $this->dm->persist($order2);
        $this->dm->persist($order3);
        $this->dm->persist($order4);
        $this->dm->flush();
    }

    private function createQueryParams(): QueryParams
    {
        return new QueryParams(Order::class, 0, OrderQBE::class);
    }

    public function testSearch(): void
    {
        $params = self::createQueryParams();
        $inquiry = new Inquiry($this->dm);

        $qb = new ArrayQueryBuilder([]);
        $rs = iterator_to_array($inquiry->search($qb, $params));
        self::assertEquals($this->orders, $rs);
    }

    public function testPaged(): void
    {
        $params = self::createQueryParams();
        $inquiry = new Inquiry($this->dm);

        $qb = new ArrayQueryBuilder([
            fn (StepContext $ctx) => $ctx->getBuilder()->skip($ctx->isBuildForCount() ? 0 : 2),
        ]);

        self::assertEquals(new PagedData([$this->orders[2], $this->orders[3]], 4), $inquiry->paged($qb, $params));
    }
}
