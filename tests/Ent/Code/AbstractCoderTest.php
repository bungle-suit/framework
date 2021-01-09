<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Code;

use Bungle\Framework\Ent\Code\AbstractCoder;
use Bungle\Framework\Ent\Code\CodeContext;
use Bungle\Framework\Ent\Code\CoderStepInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery;
use stdClass;

class AbstractCoderTest extends MockeryTestCase
{
    /** @noinspection PhpUnusedParameterInspection */
    public function test(): void
    {
        $steps = [
            $c1 = Mockery::mock(CoderStepInterface::class),
            $c2 = Mockery::mock(CoderStepInterface::class),
            $c3 = Mockery::mock(CoderStepInterface::class),
        ];

        $ctx = new CodeContext();
        $o = new StdClass();
        $steps[] = function (StdClass $o, CodeContext $context): void {
            $context->addSection('4');
        };
        $c1->expects('__invoke')->with($o, $ctx)->andReturnNull();
        $c2->expects('__invoke')->with($o, $ctx)->andReturn('2nd');
        $c3->expects('__invoke')->with($o, $ctx)->andReturn('3rd');

        $coder = new class($steps) extends AbstractCoder {};
        self::assertEquals('2nd-3rd-4', $coder($o, $ctx));
    }
}
