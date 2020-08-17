<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Export\ExcelWriter;

use Bungle\Framework\Export\ExcelWriter\ExcelCell;
use DateTime;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExcelCellTest extends MockeryTestCase
{
    public function testNewDateCell(): void
    {
        $cell = ExcelCell::newDateCell(new DateTime('2020-07-20'), [2, 1]);
        self::assertEquals(44032, $cell->value);
        self::assertEquals([
            ExcelCell::OPTION_FORMAT_CODE => NumberFormat::FORMAT_DATE_YYYYMMDD,
        ], $cell->options);
        self::assertEquals([2, 1], $cell->span);

        $cell = ExcelCell::newDateCell(null, [3, 1]);
        self::assertEquals('', $cell->value);
        self::assertEquals([3, 1], $cell->span);
    }
}
