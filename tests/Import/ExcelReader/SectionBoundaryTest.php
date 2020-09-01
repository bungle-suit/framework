<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader;

use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Import\ExcelReader\SectionBoundary;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SectionBoundaryTest extends MockeryTestCase
{
    public function test(): void
    {
        $reader = Mockery::Mock(ExcelReader::class);
        list($startHit, $endHit) = [0, 0];
        $isStart = function (ExcelReader $r) use ($reader, &$startHit) {
            self::assertSame($r, $reader);
            $startHit ++;
            return false;
        };

        $isEnd = function (ExcelReader $r) use ($reader, &$endHit) {
            self::assertSame($r, $reader);
            $endHit ++;
            return true;
        };

        $b = new SectionBoundary($isStart, $isEnd);
        self::assertFalse($b->isSectionStart($reader));
        self::assertEquals(1, $startHit);
        self::assertEquals(0, $endHit);

        self::assertTrue($b->isSectionEnd($reader));
        self::assertEquals(1, $endHit);
    }
}
