<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests;

use ArrayIterator;
use Bungle\Framework\FP;
use Bungle\Framework\FuncInterface;
use LogicException;
use Mockery;

it('get attr', function () {
    $f = FP::attr('name');
    $o = (object)['id' => 1, 'name' => 'foo'];
    expect($f($o))->toEqual('foo');
});

it('getter', function () {
    $o = new class {
        public function getName(): string
        {
            return 'bar';
        }
    };
    $f = FP::getter('getName');
    expect($f($o))->toEqual('bar');
});

it('T', function () {
    $f = FP::t();
    expect($f())->toBeTrue();
});

it('F', function () {
    $f = FP::f();
    expect($f())->toBeFalse();
});

it('null', function () {
    $f = FP::null();
    expect($f())->toEqual(null);
});

it('group', function () {
    $arr = range(0, 10);
    self::assertEquals(
        [
            0 => [0, 2, 4, 6, 8, 10],
            1 => [1, 3, 5, 7, 9],
        ],
        FP::group(fn(int $v) => $v % 2, $arr)
    );
});

it('equal group', function () {
    $arr = range(1, 10);
    expect(FP::equalGroup(fn(int $a, int $b) => $a + 5 === $b, $arr))->toEqual(
        [
            [1, 6],
            [2, 7],
            [3, 8],
            [4, 9],
            [5, 10],
        ]
    );
});

it('any', function () {
    // empty always return false
    expect(FP::any(fn(int $v) => true, []))->toBeFalse();

    expect(FP::any(fn(int $v) => $v % 2 === 0, [1, 3, 6, 9]))->toBeTrue();
    expect(FP::any(fn(int $v) => $v % 2 === 0, [1, 9, 111]))->toBeFalse();
});

it('all', function () {
    // empty always return true
    expect(FP::all(fn(int $v) => false, []))->toBeTrue();

    expect(FP::all(fn(int $v) => $v % 2 === 0, [1, 3, 6, 9]))->toBeFalse();
    expect(FP::all(fn(int $v) => $v % 2 === 1, [1, 9, 111]))->toBeTrue();
});

it('is empty', function () {
    expect(FP::isEmpty([]))->toBeTrue();
    expect(FP::isEmpty([1]))->toBeFalse();
    expect(FP::isEmpty(new ArrayIterator([1])))->toBeFalse();
});

it('identity', function () {
    expect(FP::identity(3))->toEqual(3);
});

it('zero', function () {
    expect(FP::zero())->toEqual(0);
});

it('constant', function () {
    $one = FP::constant(1);
    expect($one())->toEqual(1);

    $foo = FP::constant('foo');
    expect($foo())->toEqual('foo');
});

it('init variable', function () {
    $a = 'foo';
    expect(FP::initVariable($a, FP::constant(0)))->toEqual('foo');
    expect($a)->toEqual('foo');

    $a = '';
    expect(FP::initVariable($a, FP::constant('foo')))->toEqual('foo');
    expect($a)->toEqual('foo');

    $a = 'foo';
    expect(FP::initVariable($a, FP::constant('bar'), fn($v) => $v === 'foo'))->toEqual('bar');
});

it('init property', function () {
    $o = (object)[];
    expect(FP::initProperty($o, 'a', FP::constant('foo')))->toEqual('foo');
    expect($o->a)->toEqual('foo');
    expect(FP::initProperty($o, 'a', FP::constant('foo')))->toEqual('foo');
    expect($o->a)->toEqual('foo');

    expect(FP::initProperty($o, 'a', FP::constant('bar'), fn($v) => $v === 'foo'))->toEqual('bar');
    expect($o->a)->toEqual('bar');

    // Ignore fIsUninitialized if the property is unset.
    expect(FP::initProperty($o, 'b', FP::constant('blah'), fn($v) => $v ===
        'foo'))->toEqual('blah');
});

it('init array item', function () {
    $arr = [];
    expect(FP::initArrayItem($arr, 3, FP::constant('foo')))->toEqual('foo');
    expect($arr[3])->toEqual('foo');

    expect(FP::initArrayItem($arr, 'a', FP::constant('bar'), fn($v) => $v ===
        'foo'))->toEqual('bar');
    expect(FP::initArrayItem($arr, 'a', FP::constant(345), fn($v) => $v === 'bar'))->toEqual(345);
    expect($arr['a'])->toEqual('345');
});

it('to keyed', function () {
    expect(FP::toKeyed(fn(int $v): int => $v, []))->toEqual([]);

    $arr = [['foo', 1], ['bar', 2]];
    expect(FP::toKeyed(fn($v) => $v[0], $arr))->toEqual(
        [
            'foo' => ['foo', 1],
            'bar' => ['bar', 2],
        ]
    );
});

it('get or create', function () {
    $arr = [];
    $f = fn(int $k) => (string)$k;
    expect(FP::getOrCreate($arr, 3, $f))->toEqual('3');
    expect($arr)->toEqual([3 => '3']);
    expect(FP::getOrCreate($arr, 3, $f))->toEqual('3');
    expect($arr)->toEqual([3 => '3']);
});

it('first or null on iterator', function () {
    $emptyIter = new ArrayIterator([]);
    expect(FP::firstOrNull(FP::t(), $emptyIter))->toBeNull();
    expect(FP::first(FP::t(), $emptyIter, 33))->toEqual(33);
    expect(FP::firstOrNull(fn($v) => $v === 4, new ArrayIterator(range(1, 10))))->toEqual(4);
    expect(FP::firstOrNull(FP::f(), range(1, 10)))->toBeNull();
});

it('first or null on array', function () {
    expect(FP::firstOrNull(fn(int $v) => true, []))->toBeNull();
    expect(FP::first(FP::t(), [], 33))->toEqual(33);
    expect(FP::firstOrNull(fn($v) => $v === 4, range(1, 10)))->toEqual(4);
});

it('not null', function () {
    expect(FP::notNull(1))->toEqual(1);

    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('Expect value not null');

    FP::notNull(null);
});

it('not null with message', function () {
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('foo');

    FP::notNull(null, 'foo');
});

it('and', function () {
    [$aHit, $bHit] = [0, 0];
    $a = function (int $a, int $b) use (&$aHit): bool {
        $aHit++;
        expect($a)->toEqual(1);
        expect($b)->toEqual(2);

        return true;
    };

    $b = function (int $a, int $b) use (&$bHit): bool {
        $bHit++;
        expect($a)->toEqual(1);
        expect($b)->toEqual(2);

        return true;
    };

    $a1 = function (int $a, int $b) use (&$aHit): bool {
        $aHit++;
        expect($a)->toEqual(1);
        expect($b)->toEqual(2);

        return false;
    };

    // case 1: $a is true, returns $b
    $f = FP::and($a, $b);
    expect($f(1, 2))->toBeTrue();

    // case 2: $a is false, $b not called.
    $f = FP::and($a1, $b);
    expect($f(1, 2))->toBeFalse();

    expect($aHit)->toEqual(2);
    expect($bHit)->toEqual(1);
});

it('or', function () {
    [$aHit, $bHit] = [0, 0];
    $a = function (array $v) use (&$aHit): bool {
        $aHit++;
        expect($v)->toEqual([1, 2]);

        return true;
    };

    $b = function (array $v) use (&$bHit): bool {
        $bHit++;
        expect($v)->toEqual([1, 2]);

        return true;
    };

    $a1 = function (array $v) use (&$aHit): bool {
        $aHit++;
        expect($v)->toEqual([1, 2]);

        return false;
    };

    // case 1: $a is true, returns $a, $b not called
    $f = FP::or($a, $b);
    expect($f([1, 2]))->toBeTrue();
    expect($bHit)->toEqual(0);

    // case 2: $a is false, return $b result.
    $f = FP::or($a1, $b);
    expect($f([1, 2]))->toBeTrue();

    expect($aHit)->toEqual(2);
    expect($bHit)->toEqual(1);
});

it('not', function () {
    $aHit = 0;
    $a = function (int $v) use (&$aHit): bool {
        $aHit++;
        expect($v)->toEqual(200);

        return true;
    };

    $f = FP::not($a);
    expect($f(200))->toBeFalse();
    expect($aHit)->toEqual(1);
});

it('last', function () {
    expect(FP::last([null]))->toBeNull();
    expect(FP::last([1]))->toEqual(1);
    expect(FP::last([1, 2]))->toEqual(2);
});

it('last on empty', function () {
    $this->expectException(LogicException::class);

    FP::last([]);
});

it('filter', function () {
    $isOdd = fn(int $v) => $v % 2 !== 0;

    expect(iterator_to_array(FP::filter($isOdd, [0, 1, 2, 3, 4, 5, 6, 7, 8])))
        ->toEqual([1, 3, 5, 7]);
});

it('map', function () {
    $double = fn(int $v) => $v * 2;
    expect(iterator_to_array(FP::map($double, [1, 2, 3, 4])))->toEqual([2, 4, 6, 8]);
});

it('count', function () {
    /** @var int[] $vars */
    $vars = [];
    expect(FP::count($vars))->toEqual(0);
    expect(FP::count(range(0, 2)))->toEqual(3);
});

it('if', function () {
    $f = FP::if(
        fn(int $a, int $b) => ($a + $b) % 2 === 0,
        fn(int $a, int $b) => $a + $b,
        fn(int $a, int $b) => $a * $b,
    );

    expect($f(3, 4))->toEqual(12);
    expect($f(2, 4))->toEqual(6);
});

it('select only on default case', function () {
    $f = FP::select(fn(int $v) => $v + 1, fn(int $v) => $v + 100);
    expect($f(50))->toEqual(150);
});

it('select', function () {
    $f = FP::select(
        $fPrep = Mockery::mock(FuncInterface::class),
        10,
        $fCase1 = Mockery::namedMock('case1', FuncInterface::class),
        $fSelect2 = Mockery::namedMock('select2', FuncInterface::class),
        $fCase2 = Mockery::namedMock('case2', FuncInterface::class),
        $fDefaultCase = Mockery::namedMock('defaultCase', FuncInterface::class),
    );

    // equal to value
    $fPrep->expects('__invoke')->with(100)->andReturn(10);
    $fCase1->expects('__invoke')->with(100)->andReturn('foo');
    expect($f(100))->toEqual('foo');

    // matched by select func
    $fPrep->expects('__invoke')->with(100)->andReturn(11);
    $fSelect2->expects('__invoke')->with(100)->andReturnTrue();
    $fCase2->expects('__invoke')->with(100)->andReturn('bar');
    expect($f(100))->toEqual('bar');

    // fall over to default case
    $fPrep->expects('__invoke')->with(100)->andReturn(11);
    $fSelect2->expects('__invoke')->with(100)->andReturnFalse();
    $fDefaultCase->expects('__invoke')->with(100)->andReturn('foobar');
    expect($f(100))->toEqual('foobar');
});

it('once', function () {
    $inner = Mockery::mock(FuncInterface::class);
    $f = FP::once($inner);
    $inner->expects('__invoke')->with()->andReturn('foo')->once();

    expect($f())->toEqual('foo');
    expect($f())->toEqual('foo');
    expect($f())->toEqual('foo');
});

it('map keys', function () {
    $a = ['one' => 1, 'two' => 2, 'three' => 3];
    $b = FP::mapKeys(fn($x) => 'new-'.$x, $a);
    expect($b)->toEqual(['new-one' => 1, 'new-two' => 2, 'new-three' => 3]);
});

it('only item', function () {
    expect(FP::onlyItem(['b']))->toEqual('b');
    expect(FP::onlyItem(['one' => 'b']))->toEqual('b');
});

it('only item on empty', function () {
    $this->expectException(LogicException::class);
    FP::onlyItem([]);
});

it('only item more than one', function () {
    $this->expectException(LogicException::class);
    FP::onlyItem(['a', 'b']);
});

it('partition', function () {
    [$even, $odd] = FP::partition(fn($v) => $v % 2 == 0, range(1, 10));
    expect($even)->toEqual([2, 4, 6, 8, 10]);
    expect($odd)->toEqual([1, 3, 5, 7, 9]);
});
