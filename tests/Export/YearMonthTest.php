<?php

namespace Bungle\Framework\Tests\Export;

use Bungle\Framework\Export\DateRange;
use Bungle\Framework\Export\YearMonth;
use DateTime;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class YearMonthTest extends MockeryTestCase
{
    /** @dataProvider  yearMonthAndToStringProvider */
    public function testYearMonthAndToString($exp,
        $year,
        $month,
        $expYear = null,
        $expMonth = null
    ): void {
        $expYear = $expYear ?? $year;
        $expMonth = $expMonth ?? $month;
        $v = new YearMonth($year, $month);
        self::assertSame($expYear, $v->getYear());
        self::assertSame($expMonth, $v->getMonth());
        self::assertEquals($exp, $v->__toString());
    }

    public function yearMonthAndToStringProvider()
    {
        return [
            'year only' => ['2021', 2021, 0],
            'padding month' => ['2021-05', 2021, 5],
            'year month' => ['2022-12', 2022, 12],
            'month > 12' => ['2022-01', 2021, 13, 2022, 1],
            'month > 24' => ['2023-01', 2021, 25, 2023, 1],
        ];
    }

    /** @dataProvider firstLastDayProvider */
    public function testFirstLastDay($first, $nextDayOfLastDay, $y, $m = 0): void
    {
        $v = new YearMonth($y, $m);
        self::assertEquals($first, $v->getFirstDay());
        self::assertEquals($nextDayOfLastDay, $v->getNextDayOfLastDay());
    }

    public function firstLastDayProvider()
    {
        return [
            'year only' => ['2021-01-01', '2022-01-01', 2021],
            'year month' => ['2021-05-01', '2021-06-01', 2021, 5],
            'cross year' => ['2021-12-01', '2022-01-01', 2021, 12],
        ];
    }

    /** @dataProvider fromArrayProvider */
    public function testFromArray($exp, $arr): void
    {
        self::assertSame($exp, strval(YearMonth::fromArray($arr)));
    }

    public function fromArrayProvider()
    {
        return [
            'year only' => ['2021', [2021]],
            'year only null month' => ['2021', [2021, null]],
            'year only zero month' => ['2021', [2021, 0]],
            'year month' => ['2021-06', [2021, 6]],
            'year month > 12' => ['2022-02', [2021, 14]],
        ];
    }

    public function testToDateRange(): void
    {
        $v = new YearMonth(2021, 1);
        self::assertEquals(new DateRange(
                               new DateTime('2021-01-01'),
                               new DateTime('2021-01-31'),
                           ), $v->toDateRange());
    }
}
