<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry\Steps;

use Bungle\Framework\Inquiry\Builder;
use Bungle\Framework\Inquiry\QueryParams;
use Bungle\Framework\Inquiry\QueryStepInterface;
use Bungle\Framework\Inquiry\Steps\QuerySteps;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class QueryStepsTest extends MockeryTestCase
{
    public function testIfBuildForCount(): void
    {
        $inner = Mockery::mock(QueryStepInterface::class);
        $step = QuerySteps::ifBuildForCount($inner);
        $qb = Mockery::mock(QueryBuilder::class);

        // ignored if current not build for count
        $builder = new Builder($qb, new QueryParams(0, []));
        $step($builder);

        // call inner if current is build for count
        $builder->set(Builder::ATTR_BUILD_FOR_COUNT, true);
        $inner->expects('__invoke')->with($builder);
        $step($builder);
    }

    public function testBuildCount(): void
    {
        $em = Mockery::mock(EntityManagerInterface::class);
        $qb = new QueryBuilder($em);
        $qb->select(['u.a', 'u.b'])
            ->addOrderBy('u.a');
        $builder = new Builder($qb, new QueryParams(0, []));
        self::assertEquals(['u.a, u.b'], $qb->getDQLPart('select'));
        self::assertEquals([new OrderBy('u.a')], $qb->getDQLPart('orderBy'));

        QuerySteps::buildCount($builder);
        self::assertEquals(['count(0) as _count'], $qb->getDQLPart('select'));
        self::assertEquals([], $qb->getDQLPart('orderBy'));
        self::assertEquals('SELECT count(0) as _count', $qb->getDQL());
    }

    public function testBuildPaging(): void
    {
        $em = Mockery::mock(EntityManagerInterface::class);
        $qb = new QueryBuilder($em);
        $qb->select(['u.a', 'u.b'])
            ->from('entityClass', 'u');

        $builder = new Builder($qb, new QueryParams(0, []));
        QuerySteps::buildPaging($builder);
        self::assertEquals(0, $qb->getFirstResult());
        self::assertEquals(QuerySteps::PAGE_RECS, $qb->getMaxResults());

        $builder = new Builder($qb, new QueryParams(2, []));
        QuerySteps::buildPaging($builder);
        self::assertEquals(QuerySteps::PAGE_RECS * 2, $qb->getFirstResult());
        self::assertEquals(QuerySteps::PAGE_RECS, $qb->getMaxResults());
    }
}
