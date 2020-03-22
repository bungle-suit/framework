<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry\Steps;

use Bungle\Framework\Inquiry\Steps\AutoFieldConditions;
use MongoDB\BSON\Regex;

class AutoFieldConditionsTest extends BaseStepTest
{
    public function test()
    {
        $this->params->qbe = (object) [
            'ignoreZero' => 0,
            'name' => 'foo',
            'ignoreEmpty' => '',
            'id' => 3,
            'ignoreNull' => null,
        ];
        $this
            ->builder
            ->expects($this->exactly(2))
            ->method('field')
            ->withConsecutive(['name'], ['id'])
            ->willReturn($this->builder)
        ;
        $this
            ->builder
            ->expects($this->exactly(2))
            ->method('equals')
            ->withConsecutive([new Regex('foo')], [3])
            ->willReturn($this->builder)
        ;

        $step = new AutoFieldConditions(array_keys(get_object_vars($this->params->qbe)));
        $step($this->ctx);
    }
}
