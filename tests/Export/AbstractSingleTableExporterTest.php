<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Export;

use Bungle\Framework\Ent\BasalInfoService;
use Bungle\Framework\Export\FS;
use Bungle\Framework\Export\ParamParser\ExportContext;
use DateTime;
use Keboola\Temp\Temp;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\HttpFoundation\Request;

class AbstractSingleTableExporterTest extends MockeryTestCase
{
    private TestSingleTableExporter $exporter;
    /** @var BasalInfoService|Mockery\MockInterface  */
    private $basal;
    private Temp $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = new Temp();
        $this->basal = Mockery::mock(BasalInfoService::class);
        $this->exporter = new TestSingleTableExporter('title');
        $this->exporter->basal = $this->basal;
        $this->exporter->fs = new FS($this->tempDir->getTmpFolder());
    }

    protected function tearDown(): void
    {
        $this->tempDir->remove();

        parent::tearDown();
    }

    public function testBuildFilename(): void
    {
        $this->basal->expects('now')->andReturn(new DateTime('2020-05-09 17:55:04'));
        self::assertEquals('new-title-2020-05-09-175504.xlsx', $this->exporter->buildFilename([]));
    }

    public function testExport(): void
    {
        $this->basal->expects('now')->andReturn(new DateTime('2020-05-09 17:55:04'));
        $context = new ExportContext(new Request());
        $rec = $this->exporter->export($context);
        $fn = $rec->tempFile;
        self::assertStringContainsString('bungle-export', $fn);

        $sheet = (new Xlsx())->load($fn);
        self::assertEquals(1, $sheet->getSheetCount());
        $workSheet = $sheet->getSheet(0);
        self::assertEquals('new-title', $workSheet->getTitle());
        self::assertNotNull($workSheet->getCell('A1'));
        self::assertEquals('new-title', $workSheet->getCell('A1')->getValue());
        self::assertNotNull($workSheet->getCell('A2'));
        self::assertEquals('ID', $workSheet->getCell('A2')->getValue());
        self::assertNotNull($workSheet->getCell('B2'));
        self::assertEquals('Name', $workSheet->getCell('B2')->getValue());
        self::assertNotNull($workSheet->getCell('A3'));
        self::assertEquals(1, $workSheet->getCell('A3')->getValue());
        self::assertNotNull($workSheet->getCell('B5'));
        self::assertEquals('blah', $workSheet->getCell('B5')->getValue());

        $merges = $workSheet->getMergeCells();
        self::assertEquals('A1:B1', array_values($merges)[0]);
    }
}
