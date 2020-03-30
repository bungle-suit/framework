<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Collection;

use Bungle\Framework\Collection\CollectionUtil;
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
}
