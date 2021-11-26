<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Export;

use Bungle\Framework\Export\DateRange;
use DateTime;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class DateRangeTest extends MockeryTestCase
{
    public function test(): void
    {
        $today = new DateTime('2020-08-15');

        // case 1: both start/end exist and in three months
        $range = self::newRange('2020-01-02', '2020-04-02');
        self::assertFalse($range->outOfRange(92, $today));

        // case 2: both start/end exist but out of three months
        $range = self::newRange('2020-01-02', '2020-04-04');
        self::assertTrue($range->outOfRange(92, $today));

        // case 3: end not exist in three months
        $range = self::newRange('2020-07-15', '');
        self::assertFalse($range->outOfRange(92, $today));

        // case 4: end not exist out of three months
        $range = self::newRange('2020-04-15', '');
        self::assertTrue($range->outOfRange(92, $today));

        // case 5: no start implicit out of three months.
        $range = self::newRange('', '2020-08-01');
        self::assertTrue($range->outOfRange(92, $today));

        // case 6: both start/end not exist
        $range = self::newRange('', '');
        self::assertTrue($range->outOfRange(92, $today));

        // case 7: disabled if $maxDays is 0
        $range = self::newRange('', '');
        self::assertFalse($range->outOfRange(0, $today));
    }

    private static function newRange(string $start, string $end): DateRange
    {
        return new DateRange(self::parseDate($start), self::parseDate($end));
    }

    private static function parseDate(string $s): ?DateTime
    {
        return $s ? new DateTime($s) : null;
    }

    /** @dataProvider toStringProvider */
    public function testToString($exp, $start, $end): void
    {
        $dq = new DateRange($start, $end);
        self::assertEquals($exp, strval($dq));
    }

    public function toStringProvider()
    {
        $start = new DateTime('2021-11-01');
        $end = new DateTime('2021-11-26');

        return [
            'empty' => ['', null, null],
            'have both' => ['2021-11-01 ~ 2021-11-26', $start, $end],
            'no start' => [' ~ 2021-11-26', null, $end],
            'no end' => ['2021-11-01 ~ ', $start, null],
        ];
    }
}
