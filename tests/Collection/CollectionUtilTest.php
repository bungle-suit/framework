<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Collection;

use ArrayIterator;
use Bungle\Framework\Collection\CollectionUtil;
use Bungle\Framework\FP;
use PHPUnit\Framework\TestCase;

class CollectionUtilTest extends TestCase
{
    public function testToKeyed(): void
    {
        self::assertEquals([], CollectionUtil::toKeyed(fn ($v) => $v, []));

        $arr = [['foo', 1], ['bar', 2]];
        self::assertEquals([
            'foo' => ['foo', 1],
            'bar' => ['bar', 2],
        ], CollectionUtil::toKeyed(fn ($v) => $v[0], $arr));
    }

    public function testGetOrCreate(): void
    {
        $arr  = [];
        $f = fn (int $k) => (string)$k;
        self::assertEquals('3', CollectionUtil::getOrCreate($arr, 3, $f));
        self::assertEquals([3 => '3'], $arr);
        self::assertEquals('3', CollectionUtil::getOrCreate($arr, 3, $f));
        self::assertEquals([3 => '3'], $arr);
    }

    public function testFirstIterator(): void
    {
        $emptyIter = new ArrayIterator([]);
        self::assertNull(CollectionUtil::first(FP::t(), $emptyIter));
        self::assertEquals(33, CollectionUtil::first(FP::t(), $emptyIter, 33));
        self::assertEquals(4,
            CollectionUtil::first(
                fn ($v) => $v === 4,
                new ArrayIterator(range(1, 10))
            )
        );
        self::assertNull(CollectionUtil::first(FP::f(), range(1, 10)));
    }

    public function testFirstArray(): void
    {
        self::assertNull(CollectionUtil::first(FP::t(), []));
        self::assertEquals(33, CollectionUtil::first(FP::t(), [], 33));
        self::assertEquals(4,
            CollectionUtil::first(
                fn ($v) => $v === 4,
                range(1, 10)
            )
        );
    }
}
