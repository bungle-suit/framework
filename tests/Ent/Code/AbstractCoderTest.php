<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Code;

use Bungle\Framework\Ent\Code\AbstractCoder;
use Bungle\Framework\Ent\Code\CarriagableCoderStepInterface;
use Bungle\Framework\Ent\Code\CodeContext;
use Bungle\Framework\Ent\Code\CoderOverflowException;
use Bungle\Framework\Ent\Code\CoderStepInterface;
use LogicException;
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

        $coder = new class($steps) extends AbstractCoder {
        };
        self::assertEquals('2nd-3rd-4', $coder($o, $ctx));
    }

    public function testOverflow(): void
    {
        $steps = [
            $c1 = Mockery::mock(CarriagableCoderStepInterface::class),
            $c2 = Mockery::mock(CarriagableCoderStepInterface::class),
            $c3 = Mockery::mock(CoderStepInterface::class),
            $c4 = Mockery::mock(CoderStepInterface::class),
        ];

        $ctx = new CodeContext();
        $o = new StdClass();
        $c1->expects('__invoke')->with($o, $ctx)->andReturn('first');
        $c2->expects('__invoke')->with($o, $ctx)->andReturn('2nd');
        $c3->expects('__invoke')->with($o, $ctx)->andThrow(new CoderOverflowException());
        $c2->expects('carry')->with($o, $ctx)->andThrow(new CoderOverflowException());
        $c1->expects('carry')->with($o, $ctx)->andReturn('newFirst');
        $c2->expects('carry')->with($o, $ctx)->andReturn('new');
        $c3->expects('__invoke')->with($o, $ctx)->andReturn('last');
        $c4->expects('__invoke')->with($o, $ctx)->andReturn('continue');

        $coder = new class($steps) extends AbstractCoder {
        };
        self::assertEquals('newFirst-new-last-continue', $coder($o, $ctx));
    }

    public function testOverflowAllFailed(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('All CarriagableCode out of code space');

        $steps = [
            $c1 = Mockery::mock(CarriagableCoderStepInterface::class),
            $c2 = Mockery::mock(CarriagableCoderStepInterface::class),
            $c3 = Mockery::mock(CoderStepInterface::class),
        ];

        $ctx = new CodeContext();
        $o = new StdClass();
        $c1->expects('__invoke')->with($o, $ctx)->andReturn('first');
        $c2->expects('__invoke')->with($o, $ctx)->andReturn('2nd');
        $c3->expects('__invoke')->with($o, $ctx)->andThrow(new CoderOverflowException());
        $c2->expects('carry')->with($o, $ctx)->andThrow(new CoderOverflowException());
        $c1->expects('carry')->with($o, $ctx)->andThrow(new CoderOverflowException());

        $coder = new class($steps) extends AbstractCoder {
        };
        $coder($o, $ctx);
    }
}
