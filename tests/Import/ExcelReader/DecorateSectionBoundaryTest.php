<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader;

use Bungle\Framework\Import\ExcelReader\DecorateSectionBoundary;
use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Import\ExcelReader\SectionBoundaryInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class DecorateSectionBoundaryTest extends MockeryTestCase
{
    public function test__construct(): void
    {
        $inner = Mockery::mock(SectionBoundaryInterface::class);
        $b = new DecorateSectionBoundary($inner);
        $reader = Mockery::mock(ExcelReader::class);

        $inner->expects('isSectionStart')->with($reader)->andReturnTrue();
        self::assertTrue($b->isSectionStart($reader));

        $inner->expects('isSectionEnd')->with($reader)->andReturnFalse();
        self::assertFalse($b->isSectionEnd($reader));

        $inner->expects('onReadComplete')->with($reader);
        $b->onReadComplete($reader);
    }
}
