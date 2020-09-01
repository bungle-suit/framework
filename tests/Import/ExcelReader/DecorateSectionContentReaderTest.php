<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader;

use Bungle\Framework\Import\ExcelReader\DecorateSectionContentReader;
use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Import\ExcelReader\SectionContentReaderInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class DecorateSectionContentReaderTest extends MockeryTestCase
{
    public function test(): void
    {
        $inner = Mockery::mock(SectionContentReaderInterface::class);
        $r = new DecorateSectionContentReader($inner);
        $reader = Mockery::mock(ExcelReader::class);

        $inner->expects('onSectionStart')->with($reader);
        $r->onSectionStart($reader);

        $inner->expects('readRow')->with($reader);
        $r->readRow($reader);

        $inner->expects('onSectionEnd')->with($reader);
        $r->onSectionEnd($reader);
    }
}
