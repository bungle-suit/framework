<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader\LabelledReader;

use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Import\ExcelReader\LabelledReader\Context;
use Bungle\Framework\Import\ExcelReader\LabelledReader\LabelledReader;
use Bungle\Framework\Import\ExcelReader\LabelledReader\LabelledValue;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class LabelledReaderTest extends MockeryTestCase
{
    public function test(): void
    {
        $book = new Spreadsheet();
        $reader = new ExcelReader($book);
        $sheet = $reader->getSheet();
        $obj = (object)['foo1' => '', 'bar' => '', 'foobar' => '', 'name'=>123];
        $context = Mockery::type(Context::class);
        /** @phpstan-var LabelledReader<object> $r */
        $r = new LabelledReader($obj, 2, 'C');
        $r->defineValue($lv1 = Mockery::mock(LabelledValue::class))
          ->defineValue($lv2 = Mockery::mock(LabelledValue::class))
          ->defineValue($lv3 = Mockery::mock(LabelledValue::class))
          ->defineValue($lv4 = Mockery::mock(LabelledValue::class));
        $lv1->allows('labelMatches')->with('foo')->andReturnTrue();
        $lv2->allows('labelMatches')->with('bar')->andReturnTrue();
        $lv3->allows('labelMatches')->with('foobar')->andReturnTrue();
        $lv4->allows('labelMatches')->with('fill')->andReturnTrue();
        $lv1->allows('labelMatches')->andReturnFalse();
        $lv2->allows('labelMatches')->andReturnFalse();
        $lv3->allows('labelMatches')->andReturnFalse();
        $lv4->allows('labelMatches')->andReturnFalse();
        $lv1->expects('getMode')->andReturn(LabelledValue::MODE_READ);
        $lv2->expects('getMode')->andReturn(LabelledValue::MODE_READ);
        $lv3->expects('getMode')->andReturn(LabelledValue::MODE_READ);
        $lv4->expects('getMode')->andReturn(LabelledValue::MODE_WRITE);
        $lv1->expects('read')->with('fooValue', $context)->andReturn('alter fooValue');
        $lv2->expects('read')->with('barValue', $context)->andReturn('barValue');
        $lv3->expects('read')->with('foobarValue', $context)->andReturn('foobarValue');
        $lv4->expects('getWriteConverter')->with()->andReturn(fn ($v, Context $context) => 456);
        $lv1->allows('getPath')->andReturn('foo1');
        $lv2->allows('getPath')->andReturn('bar');
        $lv3->allows('getPath')->andReturn('foobar');
        $lv4->allows('getPath')->andReturn('name');

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

        self::assertEquals('alter fooValue', $obj->foo1);
        self::assertEquals('barValue', $obj->bar);
        self::assertEquals('foobarValue', $obj->foobar);

        // onSectionEnd
        $lv1->expects('onSectionEnd')->with($context);
        $lv2->expects('onSectionEnd')->with($context);
        $lv3->expects('onSectionEnd')->with($context);
        $lv4->expects('onSectionEnd')->with($context);
        $r->onSectionEnd($reader);

        $c = $sheet->getCell('D5', false);
        self::assertNotNull($c);
        self::assertEquals(456, $c->getValue());
    }
}
