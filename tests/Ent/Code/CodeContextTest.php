<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Code;

use Bungle\Framework\Ent\Code\CodeContext;
use PHPUnit\Framework\TestCase;

class CodeContextTest extends TestCase
{
    public function testAddSection()
    {
        $ctx = new CodeContext();
        $ctx->addSection('foo');
        self::assertEquals(['foo'], $ctx->sections);

        $ctx->addSection('bar');
        self::assertEquals(['foo', 'bar'], $ctx->sections);

        $ctx->addSection('');
        self::assertEquals(['foo', 'bar', ''], $ctx->sections);

        $ctx->addSection('', true);
        self::assertEquals(['foo', 'bar', ''], $ctx->sections);
    }
}
