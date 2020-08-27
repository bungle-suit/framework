<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader;

use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use RuntimeException;

class ExcelReaderTest extends MockeryTestCase
{
    private Spreadsheet $book;
    private ExcelReader $reader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->book = new Spreadsheet();
        $this->reader = new ExcelReader($this->book);
    }

    public function testSwitchWorksheet(): void
    {
        $this->reader->setRow(100);
        // case 1: switch to sheet exist, reset current row
        $sheet = $this->book->createSheet();
        $sheet->setTitle('foo');
        self::assertTrue($this->reader->switchWorksheet('foo'));
        self::assertSame($sheet, $this->reader->getSheet());
        self::assertEquals(1, $this->reader->getRow());

        $this->reader->setRow(100);
        // case 2: switch to sheet not exist, allow not exist, returns false, current row not changed
        self::assertFalse($this->reader->switchWorksheet('bar', true));
        self::assertSame($sheet, $this->reader->getSheet());
        self::assertEquals(100, $this->reader->getRow());

        // case 3: switch to sheet not exist, not $allowNotExist, throws RuntimeException.
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('找不到工作表: bar');
        $this->reader->switchWorksheet('bar');
    }
}
