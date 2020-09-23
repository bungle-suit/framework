<?php
declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter;

use Bungle\Framework\FP;
use LogicException;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ExcelWriter extends ExcelOperator
{
    /**
     * Create a new work sheet as current sheet, $name as its text/title.
     */
    public function newSheet(string $name): void
    {
        if ($this->book === null) {
            throw new LogicException('newSheet() requires pass Spreadsheet to constructor');
        }

        $this->sheet = $this->book->createSheet();
        $this->sheet->setTitle($name);
        $this->row = 1;
    }

    public const TITLE_STYLE_H1 = 'h1';
    public const TITLE_STYLE_H6 = 'h6';

    /**
     * @param int $nCells how many cells to merge, 0 to disable
     */
    public function writeTitle(
        string $title,
        int $nCells = 0,
        string $col = 'A',
        string $headerStyle = self::TITLE_STYLE_H1
    ): void {
        $cellAddr = "{$col}{$this->row}";
        /** @var Cell $titleCell */
        $titleCell = $this->sheet->getCell($cellAddr);
        $titleCell->setValue($title);

        $titleStyles = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'font' => [
                'bold' => true,
            ],
        ];
        $titleCell->getStyle()->applyFromArray($titleStyles);
        if ($headerStyle === self::TITLE_STYLE_H1) {
            $titleCell->getStyle()->getFont()->setSize(16);
            $this->sheet->getRowDimension($this->row)->setRowHeight(20);
        }
        if ($nCells > 1) {
            $colIdx = Coordinate::columnIndexFromString($col);
            $endCol = Coordinate::stringFromColumnIndex($colIdx + $nCells - 1);
            $this->sheet->mergeCells("$col{$this->row}:{$endCol}{$this->row}");
        }

        $this->nextRow();
    }

    /**
     * @param array<int, ExcelColumn>  $cols
     * @param iterable<object|(string|number|null)[]> $rows
     */
    public function writeTable(array $cols, iterable $rows, string $col = 'A'): void
    {
        $sheet = $this->sheet;
        $startRow = $this->row;
        $startColIdx = Coordinate::columnIndexFromString($col);

        $colCountIncludeSpan = 0;
        $idx = $startColIdx;
        /** @var ExcelColumn $c */
        foreach ($cols as $c) {
            $colCountIncludeSpan += $c->getColSpan();
            $sheet->setCellValueByColumnAndRow($idx, $this->getRow(), $c->getHeader());
            $idx += $c->getColSpan();
        }

        $sheet->getStyleByColumnAndRow(
            $startColIdx,
            $this->row,
            $startColIdx + $colCountIncludeSpan - 1,
            $this->row
        )->applyFromArray(
            [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFDDDDDD'],
                ],
            ]
        );

        $this->nextRow();

        $propertyAccessor = new PropertyAccessor();
        foreach ($rows as $idx => $row) {
            $dataRow = [];
            /** @var ExcelColumn $c */
            foreach ($cols as $c) {
                $v = $c->getPropertyPath() ? $propertyAccessor->getValue($row, $c->getPropertyPath()) : $row;
                $v = ($c->getValueConverter())($v, $idx, $row);
                $dataRow[] = $v;
                for ($i = 0; $i < ($c->getColSpan() - 1); $i++) {
                    $dataRow[] = null;
                }
            }
            $sheet->fromArray($dataRow, null, "$col{$this->row}", true);
            $this->nextRow();
        }
        /** @var ExcelColumn $col */
        foreach ($cols as $idx => $col) {
            if ($col->formulaEnabled()) {
                $f = $col->getFormula();
                $colIdx = $idx + $startColIdx;
                for ($row = $startRow + 1; $row < $this->getRow(); $row ++) {
                    $sheet->setCellValueByColumnAndRow($colIdx, $row, $f($row));
                }
            }
        }
        $firstSumCol = -1;
        foreach ($cols as $idx => $col) {
            if ($col->isEnableSum()) {
                $firstSumCol = -1 === $firstSumCol ? $idx : $firstSumCol;
                $colName = Coordinate::stringFromColumnIndex($startColIdx + $idx);
                [$firstDataRow, $lastDataRow] = [$startRow + 1, $this->row - 1];
                /** @var Cell $c */
                $c = $sheet->getCell("{$colName}{$this->row}");
                $c->setValue("=round(sum({$colName}{$firstDataRow}:{$colName}{$lastDataRow}),2)");
            }
        }
        if ($firstSumCol > 0) {
            $colName = Coordinate::stringFromColumnIndex($startColIdx + $firstSumCol - 1);
            /** @var Cell $c */
            $c = $sheet->getCell($colName.$this->row);
            $c->setValue('总计');
            $c->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        $sheet
            ->getStyleByColumnAndRow($startColIdx, $startRow, $startColIdx + $colCountIncludeSpan - 1, $this->row - 1)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $colIdx = $startColIdx;
        foreach ($cols as $idx => $c) {
            $fmt = $c->getCellFormat();
            if (null !== $fmt) {
                $sheet
                    ->getStyleByColumnAndRow($startColIdx + $idx, $startRow, $startColIdx + $idx, $this->row - 1)
                    ->getNumberFormat()->setFormatCode($fmt);
            }

            if ($c->getColSpan() > 1) {
                $colName = Coordinate::stringFromColumnIndex($colIdx);
                $endColName = Coordinate::stringFromColumnIndex($colIdx + $c->getColSpan() - 1);
                foreach (range($startRow, $this->getRow() - 1) as $row) {
                    $sheet->mergeCells("$colName$row:$endColName$row");
                }
            }
            $colIdx += $c->getColSpan();
        }
    }

    /**
     * Write a grid area, grid is excel area with various cells for each row.
     * Write a border lines for each cell by default.
     *
     * @phpstan-param array<(ExcelCell|(string|number|null))[]> $cells
     */
    public function writeGrid(array $cells, string $col = 'A'): void
    {
        $startRow = $this->getRow();
        $startColIdx = Coordinate::columnIndexFromString($col);
        $maxGridCols = 0;
        foreach ($cells as $row) {
            $colIdx = $startColIdx;
            foreach ($row as $cv) {
                /** @var Cell $c */
                $c = FP::notNull($this->sheet->getCellByColumnAndRow($colIdx, $this->getRow()));
                if (!($cv instanceof ExcelCell)) {
                    $c->setValue($cv);
                    $colIdx ++;
                    continue;
                }

                $c->setValue($cv->value);
                if ($cv->span !== null) {
                    [$spanWidth, $spanHeight] = $cv->span;
                    assert($spanHeight === 1, 'Span more than one row not supported');
                    $r = $this->getRow();
                    $startCol = Coordinate::stringFromColumnIndex($colIdx);
                    $endCol = Coordinate::stringFromColumnIndex($colIdx + $spanWidth - 1);
                    $this->sheet->mergeCells("$startCol$r:$endCol$r");
                    $colIdx += $spanWidth;
                } else {
                    $colIdx++;
                }

                if ($formatCode = $cv->options[ExcelCell::OPTION_FORMAT_CODE] ?? null) {
                    $c->getStyle()->getNumberFormat()->setFormatCode($formatCode);
                }

                $maxGridCols = max($maxGridCols, $colIdx - $startColIdx);
            }
            $this->nextRow();
        }

        $startCell = $col.$startRow;
        $endCell = Coordinate::stringFromColumnIndex($startColIdx + $maxGridCols - 1).($this->getRow() - 1);
        $this->sheet
            ->getStyle("{$startCell}:{$endCell}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
        ;
    }
}
