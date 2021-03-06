<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Model;

use Bungle\Framework\Model\ArrayUtil;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ArrayUtilTest extends MockeryTestCase
{
    public function testInsertAt(): void
    {
        // empty array
        $arr = [];
        ArrayUtil::insertAt($arr, 0, 1);
        self::assertEquals([1], $arr);

        // int key
        $arr = [1, 2, 3];
        ArrayUtil::insertAt($arr, 1, 10);
        self::assertEquals([1, 10, 2, 3], $arr);

        // int items
        $arr = [1, 2, 3];
        ArrayUtil::insertAt($arr, 1, [10, 20]);
        self::assertEquals([1, 10, 20, 2, 3], $arr);

        // string key
        $arr = ['a' => 1, 'b' => 2, 'c' => 3];
        ArrayUtil::insertAt($arr, 'b', ['aa' => 10, 'bb' => 20]);
        self::assertEquals(['a' => 1, 'aa' => 10, 'bb' => 20, 'b' => 2, 'c' => 3], $arr);
    }

    public function testRemoveElement(): void
    {
        $arr = [1, 2, 3, 4];
        self::assertTrue(ArrayUtil::removeElement($arr, 2));
        self::assertEquals([0 => 1, 2 => 3, 3 => 4], $arr);

        self::assertFalse(ArrayUtil::removeElement($arr, 2, true));
        self::assertEquals([0 => 1, 2 => 3, 3 => 4], $arr);

        self::assertTrue(ArrayUtil::removeElement($arr, 3, true));
        self::assertEquals([1, 4], $arr);

        $arr = ['a' => 1, 'b' => 2, 'c' => 3];
        self::assertTrue(ArrayUtil::removeElement($arr, 2));
        self::assertEquals(['a' => 1, 'c' => 3], $arr);

        self::assertTrue(ArrayUtil::removeElement($arr, 1, true));
        self::assertEquals([3], $arr);
    }
}
