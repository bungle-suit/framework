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
        [$startHit, $endHit] = [0, 0];
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
        self::assertFalse($sheet->cellExists('A9'));
        self::assertFalse($sheet->cellExists('B9'));

        // row looks like empty, not empty after $colDetects cols.
        $sheet->setCellValue('C9', 'blah');
        $sheet->setCellValue('A9', '');
        self::assertTrue($f($this->reader));

        // cell value 0 not empty
        $sheet->setCellValue('A9', 0);
        $sheet->setCellValue('B9', false);
    }

    /**
     * @dataProvider colIsMergedStartProvider
     * @param callable(ExcelReader): bool $f
     */
    public function testColIsMergedStart(bool $exp, string $range, int $row, callable $f): void
    {
        $sheet = $this->reader->getSheet();
        $sheet->mergeCells($range);
        $this->reader->setRow($row);
        self::assertEquals($exp, $f($this->reader));
    }

    /**
     * @return array<mixed[]>
     */
    public function colIsMergedStartProvider(): array
    {
        $f1 = SectionBoundary::colIsMergedStart('B');
        $f2 = SectionBoundary::colIsMergedStart('B', 4);

        return [
            [true, 'B1:C1', 1, $f1],
            // not merged
            [false, 'B1:C1', 2, $f1],
            // not merged start
            [false, 'A3:C3', 3, $f1],

            // minimal merged cells: not enough cells
            [false, 'B5:D5', 5, $f2],
            // minimal merged cells: equal minimal cells
            [true, 'B6:E6', 6, $f2],
            // minimal merged cells: more than minimal cells
            [true, 'B7:F7', 7, $f2],
        ];
    }
}
