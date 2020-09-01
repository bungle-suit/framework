<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader;

use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Import\ExcelReader\MatchFirstSectionBoundary;
use Bungle\Framework\Import\ExcelReader\SectionBoundaryInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class MatchFirstSectionBoundaryTest extends MockeryTestCase
{
    public function test(): void
    {
        $reader = Mockery::mock(ExcelReader::class);
        $inner = Mockery::mock(SectionBoundaryInterface::class);
        $b = new MatchFirstSectionBoundary($inner);

        // case 1: inner returns false
        $inner->expects('isSectionStart')->with($reader)->andReturnFalse();
        self::assertFalse($b->isSectionStart($reader));

        // case 2: inner returns true
        $inner->expects('isSectionStart')->with($reader)->andReturnTrue();
        self::assertTrue($b->isSectionStart($reader));

        // case 3: afterwords inner not called, and always returns false.
        self::assertFalse($b->isSectionStart($reader));
    }
}
