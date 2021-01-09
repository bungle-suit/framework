<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Code;

use Bungle\Framework\Ent\Code\CodeContext;
use PHPUnit\Framework\TestCase;

class CodeContextTest extends TestCase
{
    public function testAddSection(): void
    {
        $ctx = new CodeContext();
        $ctx->addSection('foo');
        self::assertEquals(['foo'], $ctx->getSections());

        $ctx->addSection('bar');
        self::assertEquals(['foo', 'bar'], $ctx->getSections());

        $ctx->addSection('');
        self::assertEquals(['foo', 'bar', ''], $ctx->getSections());

        $ctx->addSection('', true);
        self::assertEquals(['foo', 'bar', ''], $ctx->getSections());
    }

    public function testToString(): void
    {
        $ctx = new CodeContext();
        $ctx->addSection('a');
        $ctx->addSection('b');
        $ctx->addSection('c');

        // result set, returns result.
        $ctx->result = 'abc';
        self::assertEquals('abc', strval($ctx));

        // result not set, join sections up
        $ctx->result = '';
        self::assertEquals('a-b-c', strval($ctx));
        self::assertEquals('', $ctx->result);
    }
}
