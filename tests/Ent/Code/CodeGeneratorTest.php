<?php
/** @noinspection PhpParamsInspection */
declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Code;

use Bungle\Framework\Ent\Code\CodeGenerator;
use Bungle\Framework\Ent\Code\GeneratorInterface;
use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CodeGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $o1 = (object)['o'=>1];
        $o2 = (object)['o'=>2];
        $g1 = Mockery::mock(GeneratorInterface::class);
        $g2 = Mockery::mock(GeneratorInterface::class);
        $g1->expects('supports')->with($o1)->andReturn(true);
        $g1->expects('supports')->with($o2)->andReturn(false);
        $g2->expects('supports')->with($o2)->andReturn(true);
        $g1->expects('generate')->with($o1)->andReturn('foo');
        $g2->expects('generate')->with($o2)->andReturn('bar');

        $g  = new CodeGenerator([$g1, $g2]);
        self::assertEquals('foo', $g->generate($o1));
        self::assertEquals('bar', $g->generate($o2));
    }

    public function testNoGeneratorSupports(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("No code generator support class 'stdClass'");

        $o1 = (object)['o'=>1];
        $g1 = Mockery::mock(GeneratorInterface::class);
        $g1->expects('supports')->with($o1)->andReturn(false);

        $g  = new CodeGenerator([$g1]);
        self::assertEquals('foo', $g->generate($o1));
    }
}
