<?php

declare(strict_types=1);

namespace Inquiry\Steps;

use Bungle\Framework\Inquiry\QueryParams;
use Bungle\Framework\Inquiry\StepContext;
use Bungle\Framework\Inquiry\Steps\SetPaging;
use PHPUnit\Framework\TestCase;
use stdClass;

class SetPagingTest extends TestCase
{
    public function testStep()
    {
        $step = new SetPaging();
        $params = new QueryParams(Order::class, 2, new stdClass());
        $ctx = new StepContext(true, $params);
        $step($ctx);
        self::assertEquals(50, $ctx->query->offset);
        self::assertEquals(25, $ctx->query->count);
    }
}
