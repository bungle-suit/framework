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
}
