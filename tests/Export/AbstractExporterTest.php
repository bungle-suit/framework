<?php

/** @noinspection PhpParamsInspection */
declare(strict_types=1);

namespace Bungle\Framework\Tests\Export;

use ArrayIterator;
use Bungle\Framework\Export\AbstractExporter;
use Bungle\Framework\Export\ExportProgress;
use Bungle\Framework\Export\ExportResult;
use Bungle\Framework\Export\FSInterface;
use Bungle\Framework\Export\ParamParser\ExportContext;
use Bungle\Framework\Export\ParamParser\ParamValueParserInterface;
use Bungle\Framework\Func;
use Hamcrest\Matchers;
use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Traversable;

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

    private static function createExporterForProgress()
    {
        $exporter = new class() extends AbstractExporter {
            public function __construct()
            {
                $this->initProgress();
            }

            public function buildFilename(array $params): string
            {
                throw new LogicException('buildFilename not implemented');
            }

            protected function buildParamParser(): Traversable
            {
                throw new LogicException('buildParamParser not implemented');
            }

            protected function doBuild(string $fn, array $params): void
            {
                throw new LogicException('doBuild not implemented');
            }

            public function setProgressTotal(int $total): void
            {
                parent::setProgressTotal($total);
            }

            public function incProgress(int $delta = 1): void
            {
                parent::incProgress($delta);
            }

            public function sendMessage(string $message): void
            {
                parent::sendMessage($message);
            }

            public function sendStatus(string $status): void
            {
                parent::sendStatus($status);
            }
        };

        $onProgress = Mockery::mock(Func::class);
        $exporter->setOnProgress($onProgress);

        return [$exporter, $onProgress];
    }

    public function testProgress(): void
    {
        $pr = new ExportProgress();
        /**
         * @var AbstractExporter $exporter
         * @var Func|Mockery\MockInterface $onProgress
         */
        [$exporter, $onProgress] = self::createExporterForProgress();

        // progress not set
        $pr->total = -1;
        $pr->current = 1;
        $onProgress->expects('__invoke')->with(Matchers::equalTo($pr));
        $exporter->incProgress();

        // set total
        $pr->total = 100;
        $onProgress->expects('__invoke')->with(Matchers::equalTo($pr));
        $exporter->setProgressTotal(100);
        $pr->current = 2;
        $onProgress->expects('__invoke')->with(Matchers::equalTo($pr));
        $exporter->incProgress();

        // send message
        $pr->message = 'foo';
        $onProgress->expects('__invoke')->with(Matchers::equalTo($pr));
        $exporter->sendMessage('foo');
        $pr->current = 3;
        $pr->message = '';
        $onProgress->expects('__invoke')->with(Matchers::equalTo($pr));
        $exporter->incProgress();

        // send status
        $pr->status = 'foo';
        $onProgress->expects('__invoke')->with(Matchers::equalTo($pr));
        $exporter->sendStatus('foo');
        $pr->current = 4;
        $pr->status = '';
        $onProgress->expects('__invoke')->with(Matchers::equalTo($pr));
        $exporter->incProgress();
    }
}
