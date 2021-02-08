<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Code;

use Bungle\Framework\Ent\Code\CarriagableCoderStepInterface;
use Bungle\Framework\Ent\Code\CodeContext;
use Mockery;
use PHPUnit\Framework\TestCase;

class CodeContextTest extends TestCase
{
    public function testAddSection(): void
    {
        $ctx = new CodeContext();
        $ctx->addSection('foo');
        self::assertEquals(['foo'], $ctx->getSections());

        $ctx->addSection('bar');
        self::assertEquals(['foo', 'bar'], $ctx->getSections());

        $ctx->addSection('');
        self::assertEquals(['foo', 'bar', ''], $ctx->getSections());

        $ctx->addSection('', true);
        self::assertEquals(['foo', 'bar', ''], $ctx->getSections());
    }

    public function testToString(): void
    {
        $ctx = new CodeContext();
        $ctx->addSection('a');
        $ctx->addSection('b');
        $ctx->addSection('c');

        // result set, returns result.
        $ctx->result = 'abc';
        self::assertEquals('abc', strval($ctx));

        // result not set, join sections up
        $ctx->result = '';
        self::assertEquals('a-b-c', strval($ctx));
        self::assertEquals('', $ctx->result);
    }

    public function testCarriageSteps(): void
    {
        $ctx = new CodeContext();
        self::assertEquals([], $ctx->getCarriageSteps());

        $ctx->addSection('foo');
        /** @var Mockery\MockInterface&CarriagableCoderStepInterface<string> $c1 */
        $c1 = Mockery::mock(CarriagableCoderStepInterface::class);
        /** @var Mockery\MockInterface&CarriagableCoderStepInterface<string> $c2 */
        $c2 = Mockery::mock(CarriagableCoderStepInterface::class);

        $ctx->addSection('bar', false, $c1);
        $ctx->addSection('blah', false, $c2);

        self::assertEquals(
            [
                1 => $c1,
                2 => $c2,
            ],
            $ctx->getCarriageSteps()
        );
    }
}
