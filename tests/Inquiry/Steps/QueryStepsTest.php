<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry\Steps;

use Bungle\Framework\Inquiry\Builder;
use Bungle\Framework\Inquiry\QueryParams;
use Bungle\Framework\Inquiry\QueryStepInterface;
use Bungle\Framework\Inquiry\Steps\QuerySteps;
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
}
