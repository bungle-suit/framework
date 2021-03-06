<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader\TableReader;

use Bungle\Framework\Import\ExcelReader\TableReader\PatternColumnHeaderCellMatcher;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class PatternColumnHeaderCellMatcherTest extends MockeryTestCase
{
    public function testMatches(): void
    {
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet();

        $getCell = function (string $loc) use ($sheet): Cell
        {
            $r = $sheet->getCell($loc) ;
            assert($r !== null);
            return $r;
        };

        $m = new PatternColumnHeaderCellMatcher('/foo|bar/i');
        $sheet->setCellValue('A1', 'foo');
        self::assertTrue($m->matches($getCell('A1')));

        $sheet->setCellValue('A2', 'blah');
        self::assertFalse($m->matches($getCell('A2')));

        self::assertFalse($m->matches($getCell('A3')));
    }
}
