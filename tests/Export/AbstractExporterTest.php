<?php

/** @noinspection PhpParamsInspection */
declare(strict_types=1);

namespace Bungle\Framework\Tests\Export;

use ArrayIterator;
use Bungle\Framework\Export\AbstractExporter;
use Bungle\Framework\Export\ExportResult;
use Bungle\Framework\Export\FSInterface;
use Bungle\Framework\Export\ParamParser\ExportContext;
use Bungle\Framework\Export\ParamParser\ParamValueParserInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class AbstractExporterTest extends MockeryTestCase
{
    /** @var Mockery\MockInterface|AbstractExporter */
    private $exporter;
    /** @var FSInterface|Mockery\MockInterface */
    private $fs;
    private ExportContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->context = new ExportContext(new Request());
        $this->fs = Mockery::mock(FSInterface::class);
        $this->exporter = Mockery::mock(AbstractExporter::class)->makePartial();
        $this->exporter->fs = $this->fs;
        $this->exporter->shouldAllowMockingProtectedMethods();
    }

    public function testExportParamParseError(): void
    {
        $this->fs->expects('tempFile')->with('blah')->andReturn(
            'errorFile'
        );
        $this->exporter->expects('buildParamParser')
                       ->andReturn(
                           new ArrayIterator(
                               [$p1 = Mockery::mock(ParamValueParserInterface::class)]
                           )
                       );
        $p1->expects('__invoke')->andReturn('blah');
        self::assertEquals(
            new ExportResult('errorFile', 'error.txt'),
            $this->exporter->export($this->context)
        );
    }

    public function testExportExportError(): void
    {
        $this->fs->expects('tempFile')->with()->andReturn('exportFile');
        $this->fs->expects('removeFile')->with('exportFile');
        $this->fs->expects('tempFile')->with('export error')
                 ->andReturn('errorFile');
        $this->exporter->expects('buildParamParser')->andReturn(new ArrayIterator([]));
        $this->exporter->expects('doBuild')->with('exportFile', [])->andThrow(
            new RuntimeException('export error')
        );
        self::assertEquals(
            new ExportResult('errorFile', 'error.txt'),
            $this->exporter->export($this->context)
        );
    }

    public function testExportSucceed(): void
    {
        $this->fs->expects('tempFile')->with()->andReturn('exportFile');
        $this
            ->exporter
            ->expects('buildParamParser')
            ->andReturn(
                new ArrayIterator(
                    [
                        function (ExportContext $ctx) {
                            $ctx->set('a', 1);
                        },
                    ]
                )
            );
        $this->exporter->expects('buildFilename')->with(['a' => 1])->andReturn('foo.xlsx');
        $this->exporter->expects('doBuild')->with('exportFile', ['a' => 1]);

        self::assertEquals(
            new ExportResult('exportFile', 'foo.xlsx'),
            $this->exporter->export($this->context)
        );
    }
}
