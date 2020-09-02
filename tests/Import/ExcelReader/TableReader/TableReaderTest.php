<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader\TableReader;

use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Import\ExcelReader\TableReader\Column;
use Bungle\Framework\Import\ExcelReader\TableReader\Context;
use Bungle\Framework\Import\ExcelReader\TableReader\TableReader;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use RuntimeException;

class TableReaderTest extends MockeryTestCase
{
    private ExcelReader $reader;
    private array $cols;
    private TableReader $r;
    private array $arr;

    protected function setUp(): void
    {
        parent::setUp();

        $book = new Spreadsheet();
        $this->reader = new ExcelReader($book);
        $book->getActiveSheet()->setTitle('sheet 1');

        $col1 = new Column('[0]', 'lbl1');
        $col2 = new Column('[1]', 'lbl2');
        $col3 = new Column('[2]', 'lbl3');
        $this->cols = [$col1, $col2, $col3];
        $this->arr = [];
        $this->r = new TableReader($this->cols, function (array $item) {
            $this->arr[] = $item;
        }, 'C');
    }

    public function test(): void
    {
        $this->reader->setRow(2);
        $sheet = $this->reader->getSheet();
        $sheet->fromArray([
            ['lbl3', 'lbl1', '', 'lbl2'],
            ['foo', 'bar', '', 'foobar'],
            ['1', '2', '', '10'],
        ], null, 'C2');

        $r = $this->r;
        $r->onSectionStart($this->reader);
        $r->readRow($this->reader);

        $this->reader->nextRow();
        $r->readRow($this->reader);
        $this->reader->nextRow();
        $r->readRow($this->reader);

        self::assertEquals([
            ['bar', 'foobar', 'foo'],
            ['2', '10', '1'],
        ], $this->arr);

        // create item, onRowComplete
    }

    public function testOnRowComplete(): void
    {
        $sheet = $this->reader->getSheet();
        $sheet->fromArray([
            ['lbl3', 'lbl1', '', 'lbl2'],
            ['1', '2', '', '10'],
        ], null, 'C1');
        $this->r->setOnRowComplete(
            function (array &$item, Context $context) {
                assert($context !== null);
                $item['d'] = 'v';
            }
        );

        $r = $this->r;
        $r->onSectionStart($this->reader);
        $r->readRow($this->reader);

        $this->reader->nextRow();
        $r->readRow($this->reader);
        self::assertEquals([
            ['2', '10', '1', 'd' => 'v'],
        ], $this->arr);
    }

    public function testCannotFoundColumn(): void
    {
        $this->reader->setRow(3);
        $sheet = $this->reader->getSheet();
        $sheet->fromArray([
            ['lbl1', '', 'lbl2'],
        ], null, 'C3');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('工作表"sheet 1"第3行没有列"lbl3"');

        $this->r->onSectionStart($this->reader);
    }
}
