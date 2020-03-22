<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry\Steps;

use Bungle\Framework\Inquiry\Steps\SetFields;

class SetFieldsTest extends BaseStepTest
{
    public function test(): void
    {
        $this->builder->expects($this->once())->method('select')->with(['id', 'name']);
        $setField = new SetFields(['id', 'name']);
        $setField($this->ctx);
    }
}
