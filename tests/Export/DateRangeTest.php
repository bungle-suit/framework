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
        self::assertFalse($range->outOfRange($today));

        // case 2: both start/end exist but out of three months
        $range = self::newRange('2020-01-02', '2020-04-04');
        self::assertTrue($range->outOfRange($today));

        // case 3: end not exist in three months
        $range = self::newRange('2020-07-15', '');
        self::assertFalse($range->outOfRange($today));

        // case 4: end not exist out of three months
        $range = self::newRange('2020-04-15', '');
        self::assertTrue($range->outOfRange($today));

        // case 5: no start implicit out of three months.
        $range = self::newRange('', '2020-08-01');
        self::assertTrue($range->outOfRange($today));

        // case 6: both start/end not exist
        $range = self::newRange('', '');
        self::assertTrue($range->outOfRange($today));
    }

    private static function newRange(string $start, string $end): DateRange
    {
        return new DateRange(self::parseDate($start), self::parseDate($end));
    }

    private static function parseDate(string $s): ?DateTime
    {
        return $s ? new DateTime($s) : null;
    }
}
