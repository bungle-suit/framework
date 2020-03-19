<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Traits;

use PHPUnit\Framework\TestCase;

final class AttributesTest extends TestCase
{
    public function test(): void
    {
        $ctx = new TestContext();
        self::assertEmpty($ctx->all());

        self::assertFalse($ctx->has('foo'));
        $ctx->set('foo', 'bar');
        self::assertTrue($ctx->has('foo'));
        self::assertEquals('bar', $ctx->get('foo'));

        // get not exist
        self::assertNull($ctx->get('bar'));
        self::assertTrue($ctx->get('bar', true));
        // not get default on falsy value
        $ctx->set('bar', 0);
        self::assertEquals(0, $ctx->get('bar'));
        // not get default on null value
        $ctx->set('bar', null);
        self::assertNull($ctx->get('bar', 11));

        $this->assertEquals([
        'foo' => 'bar',
        'bar' => null,
        ], $ctx->all());
    }

    public function testRemove(): void
    {
        $ctx = new TestContext();
        $ctx->remove('foo');

        $ctx->set('foo', 3);
        $ctx->set('bar', 4);
        $ctx->remove('foo');
        self::assertFalse($ctx->has('foo'));

        // unset not exist
        $ctx->remove('foo');

        $this->assertEquals([
        'bar' => 4,
        ], $ctx->all());
    }
}
