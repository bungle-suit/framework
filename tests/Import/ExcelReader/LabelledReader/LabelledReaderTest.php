<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader\LabelledReader;

use Bungle\Framework\Func;
use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Import\ExcelReader\LabelledReader\Context;
use Bungle\Framework\Import\ExcelReader\LabelledReader\LabelledReader;
use Bungle\Framework\Import\ExcelReader\LabelledReader\LabelledValue;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class LabelledReaderTest extends MockeryTestCase
{
    private ExcelReader $reader;
    private Spreadsheet $book;
    private object $obj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->book = new Spreadsheet();
        $this->reader = new ExcelReader($this->book);
        $this->obj = (object)['foo1' => '', 'bar' => '', 'foobar' => '', 'name' => 123];
    }

    public function testNoValueDefined(): void
    {
        $objBak = clone $this->obj;

        $r = new LabelledReader($this->obj, 2, 'C');
        $r->onSectionStart($this->reader);
        $this->reader->setRow(2);
        $r->readRow($this->reader);
        $this->reader->nextRow();
        $r->onSectionEnd($this->reader);

        self::assertEquals($objBak, $this->obj);
    }

    public function test(): void
    {
        $reader = $this->reader;
        $sheet = $this->reader->getSheet();
        $context = Mockery::type(Context::class);
        /** @phpstan-var LabelledReader<object> $r */
        $r = new LabelledReader($this->obj, 2, 'C');
        $r->defineValue($lv1 = Mockery::mock(LabelledValue::class))
            ->defineValue($lv2 = Mockery::mock(LabelledValue::class))
            ->defineValue($lv3 = Mockery::mock(LabelledValue::class))
            ->defineValue($lv4 = Mockery::mock(LabelledValue::class));
        $lv1->allows('labelMatches')->with('foo')->andReturnTrue();
        $lv2->allows('labelMatches')->with('bar')->andReturnTrue();
        $lv3->allows('labelMatches')->with('foobar')->andReturnTrue();
        $lv4->allows('labelMatches')->with('fill')->andReturnTrue();
        $lv4->allows('getCellFormat')->andReturn(NumberFormat::FORMAT_TEXT);
        $lv1->allows('labelMatches')->andReturnFalse();
        $lv2->allows('labelMatches')->andReturnFalse();
        $lv3->allows('labelMatches')->andReturnFalse();
        $lv4->allows('labelMatches')->andReturnFalse();
        $lv1->expects('getMode')->andReturn(LabelledValue::MODE_READ);
        $lv2->expects('getMode')->andReturn(LabelledValue::MODE_READ);
        $lv3->expects('getMode')->andReturn(LabelledValue::MODE_READ);
        $lv1->expects('read')->with('fooValue', $context)->andReturn('alter fooValue');
        $lv2->expects('read')->with('barValue', $context)->andReturn('barValue');
        $lv3->expects('read')->with('foobarValue', $context)->andReturn('foobarValue');
        $lv1->allows('getPath')->andReturn('foo1');
        $lv2->allows('getPath')->andReturn('bar');
        $lv3->allows('getPath')->andReturn('foobar');
        $lv4->allows('getPath')->andReturn('name');

        $lv4->expects('getMode')->andReturn(LabelledValue::MODE_WRITE);
        $lv4->expects('getOnLabelCell')->andReturn(function (Cell $cell) {
            self::assertEquals('fill', $cell->getValue());
            $cell->setValue('new label');
        });
        $lv4->expects('getWriteConverter')->with()->andReturn(fn($v, Context $context) => 456);
        $f = Mockery::mock(Func::class);
        $f->expects('__invoke')->with(Mockery::type(Cell::class));
        $lv4->expects('getCellWriter')->andReturn($f);

        // case 1: no label matches
        $sheet->setCellValue('C2', 'unknown');
        $sheet->setCellValue('D2', '1');

        // case 2: label matches
        $sheet->setCellValue('E2', 'foo');
        $sheet->setCellValue('F2', 'fooValue');

        // case 3: label and value contains rowSpan
        $sheet->setCellValue('C3', 'bar');
        $sheet->mergeCells('C3:D3');
        $sheet->setCellValue('E3', 'barValue');
        $sheet->mergeCells('E3:G3');

        // case 4: value contains rowSpan
        $sheet->setCellValue('H3', 'foobar');
        $sheet->setCellValue('I3', 'foobarValue');

        // case 5: ignore empty line
        $sheet->setCellValue('I4', '');

        // write
        $sheet->setCellValue('C5', 'fill');

        $r->onSectionStart($reader);
        $reader->setRow(2);
        $r->readRow($reader);
        $reader->nextRow();

        $r->readRow($reader);
        $reader->nextRow();

        $r->readRow($reader);
        $reader->nextRow();

        $r->readRow($reader);
        $reader->nextRow();

        self::assertEquals('alter fooValue', $this->obj->foo1);
        self::assertEquals('barValue', $this->obj->bar);
        self::assertEquals('foobarValue', $this->obj->foobar);

        // onSectionEnd
        $lv1->expects('onSectionEnd')->with($context);
        $lv2->expects('onSectionEnd')->with($context);
        $lv3->expects('onSectionEnd')->with($context);
        $lv4->expects('onSectionEnd')->with($context);
        $r->onSectionEnd($reader);

        self::assertTrue($sheet->cellExists('D5'));
        $c = $sheet->getCell('D5');
        self::assertEquals(456, $c->getValue());
        self::assertEquals(
            NumberFormat::FORMAT_TEXT,
            $c->getStyle()->getNumberFormat()
                ->getFormatCode()
        );
        self::assertTrue($sheet->cellExists('C5'));
        $c = $sheet->getCell('C5');
        self::assertEquals('new label', $c->getValue());
    }
}
