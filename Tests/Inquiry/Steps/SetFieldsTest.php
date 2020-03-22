<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry\Steps;

use Bungle\Framework\Inquiry\QueryParams;
use Bungle\Framework\Inquiry\StepContext;
use Bungle\Framework\Inquiry\Steps\SetFields;
use Bungle\Framework\Tests\Inquiry\Order;
use Doctrine\ODM\MongoDB\Query\Builder;
use PHPUnit\Framework\TestCase;
use stdClass;

class SetFieldsTest extends TestCase
{
    public function test__invoke()
    {
        $builder = $this->createMock(Builder::class);
        $setField = new SetFields(['id', 'name']);
        $params = new QueryParams(Order::class, 0, new stdClass());
        $builder->expects($this->once())->method('select')->with(['id', 'name']);
        $ctx = new StepContext(true, $params, $builder);
        $setField($ctx);
    }
}
