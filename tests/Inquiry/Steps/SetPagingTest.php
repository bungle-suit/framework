<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry\Steps;

use Bungle\Framework\Inquiry\QueryParams;
use Bungle\Framework\Inquiry\StepContext;
use Bungle\Framework\Inquiry\Steps\SetPaging;
use Bungle\Framework\Tests\Inquiry\Order;
use Doctrine\ODM\MongoDB\Query\Builder;
use PHPUnit\Framework\TestCase;
use stdClass;

class SetPagingTest extends TestCase
{
    public function testForPaging(): void
    {
        $step = new SetPaging();
        $params = new QueryParams(Order::class, 2, new stdClass());
        $builder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('skip')->with(50);
        $builder->expects($this->once())->method('limit')->with(25);
        $ctx = new StepContext(false, $params, $builder);
        $step($ctx);
    }

    public function testForCount(): void
    {
        $step = new SetPaging();
        $params = new QueryParams(Order::class, 2, new stdClass());
        $builder = $this->createMock(Builder::class);
        $builder->expects($this->never())->method('skip');
        $builder->expects($this->never())->method('limit');
        $ctx = new StepContext(true, $params, $builder);
        $step($ctx);
    }
}
