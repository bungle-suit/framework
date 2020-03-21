<?php

declare(strict_types=1);

namespace Inquiry\Steps;

use Bungle\Framework\Inquiry\QueryParams;
use Bungle\Framework\Inquiry\StepContext;
use Bungle\Framework\Inquiry\Steps\SetFields;
use PHPUnit\Framework\TestCase;
use stdClass;

class SetFieldsTest extends TestCase
{
    public function test__invoke()
    {
        $setField = new SetFields(['id', 'name']);
        $params = new QueryParams(Order::class, 0, new stdClass());
        $ctx = new StepContext(true, $params);
        $setField($ctx);
        self::assertEquals(['id', 'name'], $ctx->query->fields);
    }
}
