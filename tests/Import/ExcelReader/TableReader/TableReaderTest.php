<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader\TableReader;

use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Import\ExcelReader\TableReader\Column;
use Bungle\Framework\Import\ExcelReader\TableReader\Context;
use Bungle\Framework\Import\ExcelReader\TableReader\TableReader;
use Bungle\Framework\Import\ExcelReader\TableReader\TableReadException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\RichText\TextElement;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use RuntimeException;

class TableReaderTest extends MockeryTestCase
{
    private ExcelReader $reader;
    /** @var Column[] */
    private array $cols;
    /** @phpstan-var TableReader<mixed[]> */
    private TableReader $r;
    /** @phpstan-var array<mixed[]> */
    private array $arr;
    private Column $col2;

    protected function setUp(): void
    {
        parent::setUp();

        $book = new Spreadsheet();
        $this->reader = new ExcelReader($book);
        $book->getActiveSheet()->setTitle('sheet 1');

        $col1 = new Column('[0]', 'lbl1');
        $this->col2 = new Column('[1]', 'lbl2');
        $col3 = new Column('[2]', 'lbl3');
        $this->cols = [$col1, $this->col2, $col3];
        $this->arr = [];
        $this->r = new TableReader(
            $this->cols,
            function (array $item): void {
                $this->arr[] = $item;
            },
            'C'
        );
        $this->r->setCreateItem(
            function (TableReader $reader) {
                self::assertEquals($this->r, $reader);

                return [];
            }
        );
    }

    private static function newRichText(string $text)
    {
        $r = new RichText();
        $r->addText(new TextElement($text));

        return $r;
    }

    public function test(): void
    {
        $this->reader->setRow(2);
        $sheet = $this->reader->getSheet();
        $sheet->fromArray(
            [
                ['lbl3', 'lbl1', '', self::newRichText('lbl2')],
                ['foo', 'bar', '', self::newRichText('foobar')],
                ['1', '2', '', '10'],
            ],
            null,
            'C2'
        );

        $r = $this->r;
        $r->onSectionStart($this->reader);
        $r->readRow($this->reader);

        $this->reader->nextRow();
        $r->readRow($this->reader);
        $this->reader->nextRow();
        $r->readRow($this->reader);

        self::assertSame(
            [
                [2 => 'foo', 0 => 'bar', 1 => 'foobar'],
                [2 => 1, 0 => 2, 1 => 10],
            ],
            $this->arr
        );

        self::assertSame([2 => 'lbl3', 0 => 'lbl1', 1 => 'lbl2'], $r->getColumnTexts());
        self::assertEquals([
            [$this->cols[2], 'C'],
            [$this->cols[0], 'D'],
            [$this->cols[1], 'F'],
        ], $r->getColumnLocations());
    }

    public function testNewIsColumnEmpty(): void
    {
        $r = $this->r;
        $f = $r->newIsColumnEmpty($this->col2);

        $this->reader->setRow(2);
        $sheet = $this->reader->getSheet();
        $sheet->fromArray(
            [
                ['lbl3', 'lbl1', '', 'lbl2'],
                ['foo', 'bar', '', ''],
                ['foo', 'bar', '', null],
                ['1', '2', '', '10'],
            ],
            null,
            'C2'
        );

        // before section start, always return false
        self::assertFalse($f($this->reader));

        // on header row, return false
        $r->onSectionStart($this->reader);
        $r->readRow($this->reader);
        self::assertFalse($f($this->reader));
        $this->reader->nextRow();

        self::assertTrue($f($this->reader));
    }

    public function testReadRowErrors(): void
    {
        $this->reader->setRow(2);
        $sheet = $this->reader->getSheet();
        $sheet->fromArray(
            [
                ['lbl3', 'lbl1', '', 'lbl2'],
                ['foo', 'bar', '', 'foobar'],
                ['1', '2', '', '10'],
            ],
            null,
            'C2'
        );
        $this->col2->setConverter(function ($v) {
            throw new RuntimeException(strval($v));
        });

        $r = $this->r;
        $r->onSectionStart($this->reader);
        $r->readRow($this->reader);

        $this->reader->nextRow();
        $r->readRow($this->reader);
        $this->reader->nextRow();
        $r->readRow($this->reader);

        self::assertEquals([], $this->arr);
        self::expectException(TableReadException::class);
        self::expectExceptionMessage(<<<msg
            导入Excel出现错误:

            工作表"sheet 1"单元格F3: foobar
            工作表"sheet 1"单元格F4: 10
            msg
        );
        $r->onSectionEnd($this->reader);
    }

    public function testOnRowComplete(): void
    {
        $sheet = $this->reader->getSheet();
        $sheet->fromArray(
            [
                ['lbl3', 'lbl1', '', 'lbl2'],
                ['1', '2', '', '10'],
            ],
            null,
            'C1'
        );
        $this->r->setOnRowComplete(
            function (array &$item, Context $context): void {
                assert($context !== null);
                $item['d'] = 'v';
            }
        );

        $r = $this->r;
        $r->onSectionStart($this->reader);
        $r->readRow($this->reader);

        $this->reader->nextRow();
        $r->readRow($this->reader);
        self::assertEquals(
            [
                ['2', '10', '1', 'd' => 'v'],
            ],
            $this->arr
        );
    }

    public function testCannotFoundColumn(): void
    {
        $this->reader->setRow(3);
        $sheet = $this->reader->getSheet();
        $sheet->fromArray(
            [
                ['lbl1', '', 'lbl2'],
            ],
            null,
            'C3'
        );
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('工作表"sheet 1"第3行没有列"lbl3"');

        $this->r->onSectionStart($this->reader);
    }

    public function testOptionalColumn(): void
    {
        self::assertFalse($this->col2->isOptional());
        $this->col2->setOptional();
        self::assertTrue($this->col2->isOptional());

        $this->reader->setRow(2);
        $sheet = $this->reader->getSheet();
        $sheet->fromArray(
            [
                ['lbl3', 'lbl1'],
                ['foo', 'bar'],
                ['1', '2'],
            ],
            null,
            'C2'
        );

        $r = $this->r;
        $r->onSectionStart($this->reader);
        $r->readRow($this->reader);

        $this->reader->nextRow();
        $r->readRow($this->reader);
        $this->reader->nextRow();
        $r->readRow($this->reader);

        self::assertEquals(
            [
                [0 => 'bar', 2 => 'foo'],
                [0 => '2', 2 => '1'],
            ],
            $this->arr
        );
    }
}
