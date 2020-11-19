<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Export\ExcelWriter;

use Bungle\Framework\Export\ExcelWriter\ExcelOperator;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExcelOperatorTest extends MockeryTestCase
{
    private ExcelOperator $op;
    private Spreadsheet $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->book = new Spreadsheet();
        $this->op = new ExcelOperator($this->book);
    }

    public function test(): void
    {
        $cur = $this->book->getSheet(0);
        $op = $this->op;
        self::assertSame($this->book, $op->getBook());
        self::assertSame($cur, $op->getSheet());

        self::assertEquals(1, $op->getRow());
        $op->setRow(100);
        self::assertEquals(100, $op->getRow());

        $op->nextRow();
        self::assertEquals(101, $op->getRow());

        $op->nextRow(10);
        self::assertEquals(111, $op->getRow());
    }

    public function testGetCellValue(): void
    {
        // cell not exist, and not auto created
        self::assertNull($this->op->getCellValue('A1'));
        self::assertNull($this->op->getSheet()->getCell('A1', false));

        // cell exist, return its value
        $this->op->getSheet()->setCellValue('B2', 'foo');
        self::assertEquals('foo', $this->op->getCellValue('B2'));

        // read formula result
        $this->op->getSheet()->setCellValue('A3', '1');
        $this->op->getSheet()->setCellValue('B3', '2');
        $this->op->getSheet()->setCellValue('C3', '=A3+B3');
        self::assertEquals(3, $this->op->getCellValue('C3'));
    }
}
