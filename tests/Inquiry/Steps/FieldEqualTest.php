<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry\Steps;

use Bungle\Framework\Inquiry\Steps\FieldEqual;

class FieldEqualTest extends BaseStepTest
{
    public function test(): void
    {
        $this->params->qbe = (object) [
            'name' => 'foo',
            'ignoreEmpty' => '',
        ];
        $this
            ->builder
            ->expects($this->once())
            ->method('field')
            ->with('name')
            ->willReturn($this->builder)
        ;

        $step = new FieldEqual('name');
        $step($this->ctx);

        $step = new FieldEqual('ignoreEmpty');
        $step($this->ctx);
    }
}
