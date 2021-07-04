<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader;

use Bungle\Framework\Import\ExcelReader\ExcelLocation;
use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Import\ExcelReader\SectionBoundary;
use Bungle\Framework\Import\ExcelReader\SectionBoundaryInterface;
use Bungle\Framework\Import\ExcelReader\SectionContentReaderInterface;
use Bungle\Framework\Import\ExcelReader\SectionReader;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Matcher\Closure;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use RuntimeException;

class ExcelReaderTest extends MockeryTestCase
{
    private Spreadsheet $book;
    private ExcelReader $reader;
    private SectionReader $sec1;
    private SectionReader $sec2;
    /** @var SectionBoundaryInterface|Mockery\MockInterface */
    private $b1;
    /** @var SectionContentReaderInterface|Mockery\MockInterface */
    private $c1;
    /** @var SectionBoundaryInterface|Mockery\MockInterface */
    private $b2;
    /** @var SectionContentReaderInterface|Mockery\MockInterface */
    private $c2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->book = new Spreadsheet();
        $this->reader = new ExcelReader($this->book);
        $this->reader->setupSections(
            [
                $this->sec1 = new SectionReader(
                    'foo',
                    $this->b1 = Mockery::namedMock('b1', SectionBoundaryInterface::class),
                    $this->c1 = Mockery::namedMock('c1', SectionContentReaderInterface::class)
                ),
                $this->sec2 = new SectionReader(
                    'bar',
                    $this->b2 = Mockery::namedMock('b2', SectionBoundaryInterface::class),
                    $this->c2 = Mockery::namedMock('c2', SectionContentReaderInterface::class)
                ),
            ]
        );

        $this->reader->getSheet()->getCell('A1');
        $this->reader->getSheet()->getCell('A2');
        $this->reader->getSheet()->getCell('A3');
        $this->reader->getSheet()->getCell('A4');
        $this->reader->getSheet()->getCell('A5');
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

    public function testSwitchWorksheetByName(): void
    {
        $this->reader->setRow(100);
        $sheet = $this->book->createSheet();
        $sheet->setTitle('bar');

        self::assertTrue($this->reader->switchWorksheet(['foo', 'bar']));
        self::assertSame($sheet, $this->reader->getSheet());
        self::assertEquals(1, $this->reader->getRow());
        $this->reader->setRow(2);

        // not found
        self::assertFalse($this->reader->switchWorksheet(['foo', 'blah'], true));
        self::assertSame($sheet, $this->reader->getSheet());
        self::assertEquals(2, $this->reader->getRow());
    }

    // case 1: match section 2, read until section end
    public function testReadUntilSectionEnd(): void
    {
        $this->b1->allows('isSectionStart')->andReturnFalse();
        $this->b1->expects('onReadComplete')->with($this->reader)->once();
        $this->b2->expects('isSectionStart')
                 ->with(self::validCurrentRow(1, 4, 5))
                 ->times(3)
                 ->andReturnFalse();
        $this->b2->expects('isSectionStart')
                 ->with(self::validCurrentRow(2))
                 ->andReturnTrue();
        $this->b2->expects('isSectionEnd')
                 ->with(self::validCurrentRow(3))
                 ->andReturnFalse();
        $this->b2->expects('isSectionEnd')
                 ->with(self::validCurrentRow(4))
                 ->andReturnTrue();
        $this->c2->expects('onSectionStart')
                 ->with(self::validCurrentRow(2));
        $this->c2->expects('readRow')
                 ->with(self::validCurrentRow(2));
        $this->c2->expects('readRow')
                 ->with(self::validCurrentRow(3));
        $this->c2->expects('onSectionEnd')
                 ->with(self::validCurrentRow(4));
        $this->b2->expects('onReadComplete')->with($this->reader)->once();

        $this->reader->read();
    }

    public function testReadUntilSheetEnd(): void
    {
        $this->b1->allows('isSectionStart')->andReturnFalse();
        $this->b1->expects('onReadComplete')->with($this->reader)->once();
        $this->b2->expects('isSectionStart')
                 ->with(self::validCurrentRow(1))
                 ->andReturnFalse();
        $this->b2->expects('isSectionStart')
                 ->with(self::validCurrentRow(2))
                 ->andReturnTrue();
        $this->b2->expects('isSectionEnd')
                 ->with(self::validCurrentRow(3, 4, 5))
                 ->times(3)
                 ->andReturnFalse();
        $this->c2->expects('onSectionStart')
                 ->with(self::validCurrentRow(2));
        $this->c2->expects('readRow')
                 ->times(4)
                 ->with(self::validCurrentRow(2, 3, 4, 5));
        $this->c2->expects('onSectionEnd')->with(self::validCurrentRow(5));
        $this->b2->expects('onReadComplete')->with($this->reader)->once();

        $this->reader->read();
    }

    public function testReadSkipEmptyRow(): void
    {
        $this->b1->expects('isSectionStart')->with(self::validCurrentRow(1))->andReturnTrue();
        $this->b1->expects('isSectionEnd')->with(self::validCurrentRow(2, 3, 4, 5))
                 ->times(4)->andReturnFalse();
        $this->sec1->setIsEmptyRow(self::expectRow(2, 4));
        $this->c1->expects('onSectionStart')->with(self::validCurrentRow(1));
        $this->c1->expects('readRow')->with(self::validCurrentRow(1, 3, 5))->times(3);
        $this->c1->expects('onSectionEnd')->with(self::validCurrentRow(5));
        $this->b1->expects('onReadComplete')->with(self::validCurrentRow(5));

        $this->b2->expects('onReadComplete')->with(self::validCurrentRow(5));

        $this->reader->read();
    }

    public function testReadTwoSections(): void
    {
        $this->b1->expects('isSectionStart')->with(self::validCurrentRow(1))->andReturnTrue();
        $this->b1->expects('isSectionStart')->with(self::validCurrentRow(3, 4))->andReturnFalse()
                 ->times(2);
        $this->b1->expects('isSectionEnd')->with(self::validCurrentRow(2))->andReturnFalse();
        $this->b1->expects('isSectionEnd')->with(self::validCurrentRow(3))->andReturnTrue();
        $this->c1->expects('onSectionStart')->with(self::validCurrentRow(1));
        $this->c1->expects('readRow')->with(self::validCurrentRow(1, 2))->times(2);
        $this->c1->expects('onSectionEnd')->with(self::validCurrentRow(3));

        $this->b2->expects('isSectionStart')->with(self::validCurrentRow(3))->andReturnFalse();
        $this->b2->expects('isSectionStart')->with(self::validCurrentRow(4))->andReturnTrue();
        $this->b2->expects('isSectionEnd')->with(self::validCurrentRow(5))->andReturnFalse();
        $this->c2->expects('onSectionStart')->with(self::validCurrentRow(4));
        $this->c2->expects('readRow')->with(self::validCurrentRow(4, 5))->times(2);
        $this->c2->expects('onSectionEnd')->with(self::validCurrentRow(5));

        $this->b1->expects('onReadComplete')->with(self::validCurrentRow(5));
        $this->b2->expects('onReadComplete')->with(self::validCurrentRow(5));

        $this->reader->read();
    }

    public function testSectionEndMatchesSectionStart(): void
    {
        $this->b1->expects('isSectionStart')->with(self::validCurrentRow(1))->andReturnTrue();
        $this->b1->expects('isSectionStart')->with(self::validCurrentRow(2, 3, 4, 5))
                 ->andReturnFalse()->times(4);
        // section end matches both row 1 and 2
        $this->b1->expects('isSectionEnd')->with(self::validCurrentRow(1, 2))->andReturnTrue();
        $this->c1->expects('onSectionStart')->with(self::validCurrentRow(1));
        $this->c1->expects('readRow')->with(self::validCurrentRow(1));
        $this->c1->expects('onSectionEnd')->with(self::validCurrentRow(2));
        $this->b2->allows('isSectionStart')->andReturnFalse();
        $this->b1->expects('onReadComplete');
        $this->b2->expects('onReadComplete');

        $this->reader->read();
    }

    public function testAbortReadOnContinueEmptyRows(): void
    {
        $this
            ->b1
            ->expects('isSectionStart')
            ->with(self::validCurrentRow(...range(1, 29)))
            ->andReturnFalse()
            ->times(29);
        $this
            ->b2
            ->expects('isSectionStart')
            ->with(self::validCurrentRow(...range(1, 29)))
            ->andReturnFalse()
            ->times(29);
        $this->b1->expects('onReadComplete')->with(self::validCurrentRow(29));
        $this->b2->expects('onReadComplete')->with(self::validCurrentRow(29));

        $this->reader->getSheet()->setCellValue('A31', 'foo');
        $this->reader->read();
    }

    public function testGetLocation(): void
    {
        $exp = new ExcelLocation('Worksheet', 1);
        self::assertEquals($exp, $this->reader->getLocation());
    }

    private static function validCurrentRow(int ...$expRows): Closure
    {
        return Mockery::on(self::expectRow(...$expRows));
    }

    /**
     * @return callable(ExcelReader): bool
     */
    private static function expectRow(int ...$expRows): callable
    {
        return fn(ExcelReader $reader) => in_array($reader->getRow(), $expRows);
    }

    public function testResolveSectionBoundary(): void
    {
        $boundary = new SectionBoundary(
            fn (ExcelReader $reader) => $reader->getRow() === 10,
            fn (ExcelReader $reader) => $reader->getRow() === 20,
        );
        $this->reader->getSheet()->setCellValue('A100', 100);
        self::assertEquals([10, 20], $this->reader->resolveSectionBoundary($boundary));
    }

    public function testResolveSectionBoundaryNotHit(): void
    {
        $boundary = new SectionBoundary(
            fn (ExcelReader $reader) => false,
            fn (ExcelReader $reader) => false,
        );
        $this->reader->getSheet()->setCellValue('A100', 100);
        self::assertNull($this->reader->resolveSectionBoundary($boundary));
    }
}
