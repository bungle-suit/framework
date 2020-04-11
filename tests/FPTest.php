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

    public function testAny(): void
    {
        // empty always return false
        self::assertFalse(FP::any(FP::t(), []));

        self::assertTrue(FP::any(fn (int $v) => $v % 2 === 0, [1, 3, 6, 9]));
        self::assertFalse(FP::any(fn (int $v) => $v % 2 === 0, [1, 9, 111]));
    }

    public function testAll(): void
    {
        // empty always return true
        self::assertTrue(FP::all(FP::f(), []));

        self::assertFalse(FP::all(fn (int $v) => $v % 2 === 0, [1, 3, 6, 9]));
        self::assertTrue(FP::all(fn (int $v) => $v % 2 === 1, [1, 9, 111]));
    }

    public function testIsEmpty(): void
    {
        self::assertTrue(FP::isEmpty([]));
        self::assertFalse(FP::isEmpty([1]));
    }

    public function testIdentity(): void
    {
        self::assertEquals(3, FP::identity(3));
    }

    public function testZero(): void
    {
        self::assertEquals(0, FP::zero());
    }

    public function testConstant(): void
    {
        $one = FP::constant(1);
        self::assertEquals(1, $one());

        $foo = FP::constant('foo');
        self::assertEquals('foo', $foo());
    }

    public function testInitVariable(): void
    {
        $a = 'foo';
        self::assertEquals('foo', FP::initVariable($a, FP::constant(0)));
        self::assertEquals('foo', $a);

        $a = '';
        self::assertEquals('foo', FP::initVariable($a, FP::constant('foo')));
        self::assertEquals('foo', $a);

        $a = 'foo';
        self::assertEquals('bar', FP::initVariable($a, FP::constant('bar'), fn ($v) => $v === 'foo'));
    }

    public function testInitProperty(): void
    {
        $o = (object)[];
        self::assertEquals('foo', FP::initProperty($o, 'a', FP::constant('foo')));
        self::assertEquals('foo', $o->a);
        self::assertEquals('foo', FP::initProperty($o, 'a', FP::constant('foo')));
        self::assertEquals('foo', $o->a);

        self::assertEquals('bar', FP::initProperty($o, 'a', FP::constant('bar'), fn ($v) => $v === 'foo'));
        self::assertEquals('bar', $o->a);

        // Ignore fIsUninitialized if the property is unset.
        self::assertEquals('blah', FP::initProperty($o, 'b', FP::constant('blah'), fn ($v) => $v === 'foo'));
    }

    public function testInitArrayItem(): void
    {
        $arr = [];
        self::assertEquals('foo', FP::initArrayItem($arr, 3, FP::constant('foo')));
        self::assertEquals('foo', $arr[3]);

        self::assertEquals('bar', FP::initArrayItem($arr, 'a', FP::constant('bar'), fn ($v) => $v === 'foo'));
        self::assertEquals(345, FP::initArrayItem($arr, 'a', FP::constant(345), fn ($v) => $v === 'bar'));
        self::assertEquals('345', $arr['a']);
    }
}
