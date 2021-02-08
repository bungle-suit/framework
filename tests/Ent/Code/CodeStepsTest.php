<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Code;

use Bungle\Framework\Ent\BasalInfoService;
use Bungle\Framework\Ent\Code\CarriagableCoderStepInterface;
use Bungle\Framework\Ent\Code\CodeContext;
use Bungle\Framework\Ent\Code\CoderStepInterface;
use Bungle\Framework\Ent\Code\CodeSteps;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use stdClass;

class CodeStepsTest extends TestCase
{
    /** @var BasalInfoService|Mockery\MockInterface */
    private $basal;
    private CodeSteps $steps;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basal = Mockery::mock(BasalInfoService::class);
        $this->steps = new CodeSteps($this->basal);
    }

    public function testLiteral(): void
    {
        $s1 = CodeSteps::literal('Foo');
        $s2 = CodeSteps::literal('Bar');
        $ctx = new CodeContext();
        $s1((object)[], $ctx);
        $s2((object)[], $ctx);

        self::assertEquals(['Foo', 'Bar'], $ctx->getSections());
    }

    public function testJoin(): void
    {
        $ctx = new CodeContext();
        $ctx->addSection('foo');
        $ctx->addSection('bar');
        $ctx->addSection('123');

        $j = CodeSteps::join('');
        $j((object)[], $ctx);
        self::assertEquals('foobar123', $ctx->result);

        // join always replace result, not append
        $j((object)[], $ctx);
        self::assertEquals('foobar123', $ctx->result);

        $j = CodeSteps::join('-');
        $j((object)[], $ctx);
        self::assertEquals('foo-bar-123', $ctx->result);
    }

    public function testDateTime(): void
    {
        $f = $this->steps->dateTime('Ymd');

        $this->basal->expects('now')->andReturn(new DateTime('2020-01-03'));
        self::assertEquals('20200103', $f());
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function testCompose(): void
    {
        $steps = [
            $c1 = Mockery::mock(CoderStepInterface::class),
            $c2 = Mockery::mock(CoderStepInterface::class),
            $c3 = Mockery::mock(CoderStepInterface::class),
            $c4 = Mockery::mock(CarriagableCoderStepInterface::class),
        ];

        $ctx = new CodeContext();
        $o = new StdClass();
        $c1->expects('__invoke')->with($o, $ctx)->andReturnNull();
        $c2->expects('__invoke')->with($o, $ctx)->andReturn('2nd');
        $c3->expects('__invoke')->with($o, $ctx)->andReturn('3rd');
        $c4->expects('__invoke')->with($o, $ctx)->andReturn('4nd');
        $steps[] = function (StdClass $o, CodeContext $context): void {
            $context->addSection('5');
        };

        $step = CodeSteps::compose($steps);
        $step($o, $ctx);
        self::assertEquals('2nd-3rd-4nd-5', strval($ctx));
        self::assertEquals([2 => $c4], $ctx->getCarriageSteps());
    }
}
