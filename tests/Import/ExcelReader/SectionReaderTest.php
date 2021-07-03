<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader;

use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Import\ExcelReader\SectionBoundaryInterface;
use Bungle\Framework\Import\ExcelReader\SectionContentReaderInterface;
use Bungle\Framework\Import\ExcelReader\SectionReader;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SectionReaderTest extends MockeryTestCase
{
    public function testGetIsEmptyRow(): void
    {
        $reader = new SectionReader(
            'foo',
            Mockery::mock(SectionBoundaryInterface::class),
            Mockery::mock(SectionContentReaderInterface::class),
        );

        $isEmptyRow = $reader->getIsEmptyRow();
        self::assertFalse($isEmptyRow(Mockery::mock(ExcelReader::class)));
    }
}
