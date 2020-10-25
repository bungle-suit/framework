<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute\DataMapper;

use Bungle\Framework\Tests\Model\ExtAttribute\TestNormalizedAttributeSet;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class NormalizedAttributesTest extends MockeryTestCase
{
    public function testArrayAccess(): void
    {
        $attrs = new TestNormalizedAttributeSet(['a' => 1, 'b' => true, 'c' => 'foo']);

        self::assertEquals(1, $attrs['a']);
        self::assertEquals(true, $attrs['b']);
        self::assertEquals('foo', $attrs['c']);

        self::assertArrayHasKey('a', $attrs);
        self::assertArrayNotHasKey('not', $attrs);

        $attrs['a'] = 2;
        self::assertEquals(2, $attrs['a']);
        $attrs['b'] = false;
        self::assertFalse($attrs['b']);
    }
}
