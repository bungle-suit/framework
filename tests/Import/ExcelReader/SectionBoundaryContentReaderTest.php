<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader;

use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Import\ExcelReader\SectionBoundaryContentReader;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SectionBoundaryContentReaderTest extends MockeryTestCase
{
    public function test(): void
    {
        $sheet = new Spreadsheet();
        $reader = new ExcelReader($sheet);
        $reader->setRow(10);

        $r = new SectionBoundaryContentReader();
        self::assertFalse($r->isHit());
        $r->onSectionStart($reader);
        self::assertTrue($r->isHit());
        self::assertEquals(10, $r->getStartRow());
        self::assertEquals(-1, $r->getEndRow());

        $reader->setRow(20);
        $r->onSectionEnd($reader);
        self::assertEquals(10, $r->getStartRow());
        self::assertEquals(20, $r->getEndRow());
    }
}
