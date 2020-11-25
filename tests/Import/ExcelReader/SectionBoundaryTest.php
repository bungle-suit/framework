<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader;

use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Import\ExcelReader\SectionBoundary;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SectionBoundaryTest extends MockeryTestCase
{
    private Spreadsheet $sheet;
    private ExcelReader $reader;

    public function test(): void
    {
        $reader = Mockery::Mock(ExcelReader::class);
        list($startHit, $endHit) = [0, 0];
        $isStart = function (ExcelReader $r) use ($reader, &$startHit) {
            self::assertSame($r, $reader);
            $startHit ++;
            return false;
        };

        $isEnd = function (ExcelReader $r) use ($reader, &$endHit) {
            self::assertSame($r, $reader);
            $endHit ++;
            return true;
        };

        $b = new SectionBoundary($isStart, $isEnd);
        self::assertFalse($b->isSectionStart($reader));
        self::assertEquals(1, $startHit);
        self::assertEquals(0, $endHit);

        self::assertTrue($b->isSectionEnd($reader));
        self::assertEquals(1, $endHit);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->sheet = new Spreadsheet();
        $this->reader = new ExcelReader($this->sheet);
    }

    public function testSheetNameIs(): void
    {
        $f = SectionBoundary::sheetNameIs('foo', 'bar');

        // current sheet not specific
        $this->reader->getSheet()->setTitle('foobar');
        self::assertFalse($f($this->reader));

        // current sheet is one of specific
        $this->reader->getSheet()->setTitle('bar');
        self::assertTrue($f($this->reader));
    }

    public function testColIs(): void
    {
        $this->reader->getSheet()->setCellValue('B2', 'bar');
        $f = SectionBoundary::colIs(['foo', 'bar'], 'B');

        // cell not one of keywords
        self::assertFalse($f($this->reader));

        // cell is one of keywords.
        $this->reader->nextRow();
        self::assertTrue($f($this->reader));
    }

    public function testRowAfter(): void
    {
        $f = SectionBoundary::rowAfter(10);

        // row before 10
        self::assertFalse($f($this->reader));
        $this->reader->setRow(9);
        self::assertFalse($f($this->reader));

        // row equals 10
        $this->reader->setRow(10);
        self::assertFalse($f($this->reader));

        // row after 10
        $this->reader->setRow(11);
        self::assertTrue($f($this->reader));
    }

    public function testRowIs(): void
    {
        $f = SectionBoundary::rowIs(3);

        $this->reader->setRow(2);
        self::assertFalse($f($this->reader));
        $this->reader->nextRow();
        self::assertTrue($f($this->reader));
        $this->reader->nextRow();
        self::assertFalse($f($this->reader));
    }

    public function testIsEmptyRow(): void
    {
        $sheet = $this->reader->getSheet();
        $f = SectionBoundary::isEmptyRow(2);

        // row after the last data row, no new cell created
        $sheet->setCellValue('A10', '');
        self::assertEquals(10, $sheet->getHighestDataRow());
        $this->reader->setRow(20);
        self::assertTrue($f($this->reader));
        self::assertEquals(10, $sheet->getHighestDataRow());

        // row before the last data row, but empty, no new cell created
        $this->reader->setRow(9);
        self::assertTrue($f($this->reader));
        self::assertNull($sheet->getCell('A9', false));
        self::assertNull($sheet->getCell('B9', false));

        // row looks like empty, not empty after $colDetects cols.
        $sheet->setCellValue('C9', 'blah');
        $sheet->setCellValue('A9', '');
        self::assertTrue($f($this->reader));

        // cell value 0 not empty
        $sheet->setCellValue('A9', 0);
        $sheet->setCellValue('B9', false);
    }

    public function testColIsMergedStart(): void
    {
        $sheet = $this->reader->getSheet();
        $f = SectionBoundary::colIsMergedStart('B');
        $sheet->getCell('B1');
        $sheet->mergeCells('B1:C1');
        self::assertTrue($f($this->reader));
        $this->reader->nextRow();
        self::assertFalse($f($this->reader));

        $sheet->getCell('B4');
        $sheet->mergeCells('A4:C4');
        $this->reader->setRow(4);
        self::assertFalse($f($this->reader));
    }
}
