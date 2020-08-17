<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Export\ParamParser;

use Bungle\Framework\Export\ParamParser\ExportContext;
use Bungle\Framework\Export\ParamParser\ParamParser;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class ParamParserTest extends MockeryTestCase
{
    private ExportContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->context = new ExportContext(new Request());
    }

    public function testParseSucceed(): void
    {
        $parser = new ParamParser([
            // case 1 return empty array
            fn (ExportContext $ctx) => null,
            // case 2 return single value
            fn (ExportContext $ctx) => $ctx->set('a', 1),
            // case 3 return another single value
            fn (ExportContext $ctx) => $ctx->set('b', 2),
            // case 4 return multi value, and override previous single value
            function (ExportContext $ctx) {
                self::assertSame($this->context, $ctx);
                $ctx->set('a', 3);
                $ctx->set('c', 4);
            },
        ]);

        self::assertEquals(['a' => 3, 'b' => 2, 'c' => 4], $parser->parse($this->context));
    }

    public function testParseFailed(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('blah blah');

        $parser = new ParamParser([
            fn (ExportContext $ctx) => $ctx->set('a', 1),
            fn (ExportContext $ctx) => 'blah blah',
            fn (ExportContext $ctx) => $ctx->set('a', 2),
        ]);

        $parser->parse($this->context);
    }
}
