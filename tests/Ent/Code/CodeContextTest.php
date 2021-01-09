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
}
