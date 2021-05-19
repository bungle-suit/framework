<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry\Steps;

use Bungle\Framework\Inquiry\Builder;
use Bungle\Framework\Inquiry\QueryParams;
use Bungle\Framework\Inquiry\Steps\QuerySteps;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class QueryStepsTest extends MockeryTestCase
{
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
