<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Export\ExcelWriter;

use Bungle\Framework\Export\ExcelWriter\ExcelCell;
use Bungle\Framework\Export\ExcelWriter\ExcelColumn;
use Bungle\Framework\Export\ExcelWriter\ExcelWriter;
use Bungle\Framework\Export\ExcelWriter\TableContext;
use Bungle\Framework\Export\ExcelWriter\TablePluginInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelWriterTest extends MockeryTestCase
{
    private Spreadsheet $sheet;
    private Worksheet $workSheet;
    private ExcelWriter $writer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sheet = new Spreadsheet();
        $this->workSheet = $this->sheet->getActiveSheet();
        $this->writer = new ExcelWriter($this->sheet);
    }

    public function testWriteTitle(): void
    {
        $this->writer->writeTitle('foo');
        $this->assertCellContent('A1', 'foo');
        self::assertEquals(2, $this->writer->getRow());
        $this->assertRow(2);

        $this->writer->writeTitle('bar', 3);
        self::assertEquals(['A2:C2'], array_values($this->workSheet->getMergeCells()));
        $cell = $this->workSheet->getCell('A2', false);
        self::assertNotNull($cell);
        self::assertEquals(
            Alignment::HORIZONTAL_CENTER,
            $cell->getStyle()->getAlignment()->getHorizontal()
        );
        self::assertTrue($cell->getStyle()->getFont()->getBold());
        self::assertEquals(16, $cell->getStyle()->getFont()->getSize());
        self::assertEquals(20, $this->workSheet->getRowDimension(2)->getRowHeight());

        $this->writer->writeTitle('foobar', 1, 'A', ExcelWriter::TITLE_STYLE_H6);
        $cell = $this->workSheet->getCell('A3', false);
        self::assertNotNull($cell);
        self::assertEquals(
            Alignment::HORIZONTAL_CENTER,
            $cell->getStyle()->getAlignment()->getHorizontal()
        );
        self::assertTrue($cell->getStyle()->getFont()->getBold());
        self::assertEquals(11, $cell->getStyle()->getFont()->getSize());
        self::assertEquals(-1, $this->workSheet->getRowDimension(3)->getRowHeight());
    }

    public function testTable(): void
    {
        $cols = [
            new ExcelColumn(
                'Foo',
                '',
                function (array $row, int $rowIdx, array $rowAgain) {
                    self::assertSame($row, $rowAgain);

                    return 10 + $row[2] + $rowIdx;
                }
            ),
            new ExcelColumn('Bar', '[1]'),
            new ExcelColumn('FooBar', '[0]'),
        ];

        $plugin = Mockery::mock(TablePluginInterface::class);
        $plugin->expects('onTableStart')->with(
            Mockery::on(fn(TableContext $ctx) => $ctx->getRowIndex() === 1)
        );
        $plugin->expects('onRowFinish')->with([12, 0, 'a'], Mockery::type(TableContext::class));
        $plugin->expects('onRowFinish')->with(
            [15, null, 'b'],
            Mockery::on(fn(TableContext $ctx) => $ctx->getRowIndex() === 3)
        );
        $plugin->expects('onTableFinish')->with(
            Mockery::on(fn(TableContext $ctx) => $ctx->getRowIndex() === 4)
        );
        $this->writer->writeTable(
            $cols,
            [
                ['a', 0, 2],
                ['b', null, 4],
            ],
            'B',
            [
                'plugins' => $plugin,
            ]
        );

        $this->assertRow(4);
        $this->assertRowContent(['Foo', 'Bar', 'FooBar'], 'B1:D1');
        $this->assertRowContent([12, 0, 'a'], 'B2:D2');
        $this->assertRowContent([15, null, 'b'], 'B3:D3');
    }

    public function testTableMergeCells(): void
    {
        $cols = [
            (new ExcelColumn('Foo', '[0]'))->setMergeCells(true)->setColSpan(2),
            new ExcelColumn('Bar', '[1]'),
        ];

        $this->writer->writeTable(
            $cols,
            [
                ['a', 1],
            ]
        );
        $this->assertRangeContent(
            [
                ['a', null, 1],
                [null, null, null],
            ],
            'A2:C3'
        );

        $this->writer->writeTable(
            $cols,
            [
                ['a', 1],
                ['a', 2],
                ['a', 3],
                ['b', 4],
                ['a', 5],
                ['a', 6],
            ]
        );

        $this->assertRangeContent(
            [
                ['Foo', null, 'Bar'],
                ['a', null, 1],
                [null, null, 2],
                [null, null, 3],
                ['b', null, 4],
                ['a', null, 5],
                [null, null, 6],
            ],
            'A3:C9'
        );
        self::assertEquals(
            ['A4:B6', 'A7:B7', 'A8:B9'],
            array_values($this->workSheet->getMergeCells())
        );
    }

    public function testSpanColumn(): void
    {
        $cols = [
            new ExcelColumn('a', '[0]'),
            (new ExcelColumn('b', '[1]'))->setColSpan(2),
            (new ExcelColumn('c', '[2]'))->setColSpan(2),
            new ExcelColumn('d', '[3]'),
        ];

        $this->writer->writeTable(
            $cols,
            [
                ['a', 0, 2, 3],
                ['b', null, 4, 5],
            ],
            'A'
        );

        $this->assertRow(4);
        $this->assertRowContent(['a', 'b', null, 'c', null, 'd'], 'A1:F1');
        $this->assertRowContent(['a', 0, null, 2, null, 3], 'A2:F2');
        $this->assertRowContent(['b', null, null, 4, null, 5], 'A3:F3');
        self::assertEquals(
            [
                'B1:C1',
                'B2:C2',
                'B3:C3',
                'D1:E1',
                'D2:E2',
                'D3:E3',
            ],
            array_values($this->workSheet->getMergeCells())
        );
        $cell = $this->workSheet->getCell('F3');
        self::assertNotNull($cell);
        self::assertEquals(
            Border::BORDER_THIN,
            $cell->getStyle()->getBorders()->getRight()->getBorderStyle()
        );
    }

    public function testFormulaColumn(): void
    {
        $cols = [
            new ExcelColumn('A', '[0]'),
            new ExcelColumn('B', '[1]'),
            (new ExcelColumn('Sum', ''))->setFormula(fn(int $row) => "=A$row+B$row"),
        ];

        $this->writer->writeTable(
            $cols,
            [
                [0, 2],
                [1, 3],
            ],
            'A'
        );

        $this->assertRow(4);
        $this->assertRowContent(['A', 'B', 'Sum'], 'A1:C1');
        $this->assertRowContent([0, 2, 2], 'A2:C2');
        $this->assertRowContent([1, 3, 4], 'A3:C3');
    }

    public function testSumColumn(): void
    {
        $cols = [
            new ExcelColumn('ID', '[0]'),
            (new ExcelColumn('Num1', '[1]'))->enableSum(),
            (new ExcelColumn('Num2', '[2]'))->enableSum(),
        ];

        $this->writer->writeTable(
            $cols,
            [
                ['a', 1, 2],
                ['b', null, 4],
            ],
            'B'
        );

        $this->assertRowContent(['a', 1, 2], 'B2:D2');
        $this->assertRowContent(['b', null, 4], 'B3:D3');
        $this->assertCellContent('B4', '总计');
        $this->assertCellContent('C4', '=round(sum(C2:C3),2)');
        $this->assertCellContent('D4', '=round(sum(D2:D3),2)');
    }

    public function testSumColumnNoRoomForSumLabel(): void
    {
        $cols = [
            (new ExcelColumn('Num1', '[0]'))->enableSum(),
            (new ExcelColumn('Num2', '[1]'))->enableSum(),
        ];

        $this->writer->writeTable(
            $cols,
            [
                [1, 2],
                [null, 4],
            ],
            'A'
        );

        $this->assertRowContent([1, 2], 'A2:B2');
        $this->assertRowContent([null, 4], 'A3:B3');
        $this->assertCellContent('A4', '=round(sum(A2:A3),2)');
        $this->assertCellContent('B4', '=round(sum(B2:B3),2)');
    }

    public function testWriteGrid(): void
    {
        $grid = [
            [
                'a',
                'b',
                new ExcelCell(
                    'c',
                    null,
                    [ExcelCell::OPTION_FORMAT_CODE => NumberFormat::FORMAT_NUMBER_00]
                ),
            ],
            [new ExcelCell('long', [2, 1]), 'd'],
        ];

        $this->writer->writeGrid($grid, 'B');
        self::assertEquals(3, $this->writer->getRow());
        $this->assertRowContent(['a', 'b', 'c'], 'B1:D1');
        $this->assertRowContent(['long', null, 'd'], 'B2:D2');

        self::assertEquals(['B2:C2'], array_values($this->workSheet->getMergeCells()));
        self::assertEquals(
            Border::BORDER_THIN,
            $this->workSheet->getStyle('B1')->getBorders()->getBottom()->getBorderStyle()
        );
        self::assertEquals(
            Border::BORDER_THIN,
            $this->workSheet->getStyle('D2')->getBorders()->getBottom()->getBorderStyle()
        );
        self::assertEquals(
            NumberFormat::FORMAT_NUMBER_00,
            $this->workSheet->getStyle('D1')->getNumberFormat()->getFormatCode()
        );
    }

    public function testNewSheet(): void
    {
        $w = new ExcelWriter($this->sheet);
        self::assertSame($this->workSheet, $w->getSheet());
        $w->setRow(100);

        $w->newSheet('foo表');
        self::assertCount(2, $this->sheet->getAllSheets());
        self::assertEquals($w->getSheet(), $this->sheet->getSheetByName('foo表'));
        self::assertEquals(1, $w->getRow());
    }

    public function testSetColumnWidths(): void
    {
        $w = new ExcelWriter($this->sheet);
        $w->setColumnWidths([1, 2, 3.5]);
        $getWidth = fn(string $col) => $this->sheet->getActiveSheet()->getColumnDimension($col)
                                                   ->getWidth();
        self::assertEquals(1, $getWidth('A'));
        self::assertEquals(2, $getWidth('B'));
        self::assertEquals(3.5, $getWidth('C'));
    }

    private function assertCellContent(string $pos, string $exp): void
    {
        $cell = $this->workSheet->getCell($pos, false);
        self::assertNotNull($cell);
        self::assertEquals($exp, $cell->getValue());
    }

    /**
     * @param mixed[] $exp
     */
    private function assertRowContent(array $exp, string $range): void
    {
        $act = $this->workSheet->rangeToArray($range)[0];
        self::assertSame($exp, $act);
    }

    /**
     * @param array<mixed[]> $exp
     */
    private function assertRangeContent(array $exp, string $range): void
    {
        $act = $this->workSheet->rangeToArray($range);
        self::assertSame($exp, $act);
    }

    private function assertRow(int $exp): void
    {
        self::assertEquals($exp, $this->writer->getRow());
    }
}
