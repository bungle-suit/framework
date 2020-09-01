<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader;

use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Import\ExcelReader\SectionContentReaderInterface;
use Bungle\Framework\Import\ExcelReader\SkipHeadRowContentReader;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SkipHeadRowContentReaderTest extends MockeryTestCase
{
    public function testReadRow(): void
    {
        $inner = Mockery::mock(SectionContentReaderInterface::class);
        $r = new SkipHeadRowContentReader($inner, 2);
        $book = new Spreadsheet();
        $reader = new ExcelReader($book);

        $reader->setRow(10);
        $inner->expects('onSectionStart')->with($reader);
        $r->onSectionStart($reader);

        // first two rows skipped
        $r->readRow($reader);
        $reader->nextRow();
        $r->readRow($reader);
        $reader->nextRow();

        // fowling rows passed to inner
        $inner->expects('readRow')->with(Mockery::on(fn (ExcelReader $reader) => $reader->getRow() === 12));
        $r->readRow($reader);
        $reader->nextRow();

        $inner->expects('readRow')->with(Mockery::on(fn (ExcelReader $reader) => $reader->getRow() === 13));
        $r->readRow($reader);
    }
}
