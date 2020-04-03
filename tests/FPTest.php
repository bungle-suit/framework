<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests;

use Bungle\Framework\FP;
use PHPUnit\Framework\TestCase;

class FPTest extends TestCase
{
    public function testAttr()
    {
        $f = FP::attr('name');
        $o = (object)['id'=>1,'name'=>'foo'];
        self::assertEquals('foo', $f($o));
    }

    public function testGetter(): void
    {
        $o = new class {
            public function getName() {
                return 'bar';
            }
        };
        $f = FP::getter('getName');
        self::assertEquals('bar', $f($o));
    }

    public function testT(): void
    {
        $f = FP::t();
        self::assertTrue($f());
    }

    public function testF(): void
    {
        $f = FP::f();
        self::assertFalse($f());
    }

    public function testGroup(): void
    {
        $arr = range(0, 10);
        self::assertEquals([
            0 => [0, 2, 4, 6, 8, 10],
            1 => [1, 3, 5, 7, 9],
        ], FP::group(fn (int $v) => $v % 2, $arr));
    }
}
