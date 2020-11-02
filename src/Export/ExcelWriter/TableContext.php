<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class TableContext
{
    private ExcelWriter $writer;
    private int $startCol;

    private int $startRow;

    /** @var ExcelColumn[] */
    private array $cols;

    /**
     * Index is the same of self::$cols.
     * @var array<int, int>
     */
    private array $colIdxes;

    /**
     * Index is the same of self::$cols.
     * @var array<int, string>
     */
    private array $colNames;

    /**
     * Index is the same of self::$cols.
     * @var array<int, string>
     */
    private array $colEndNames;

    /**
     * @var array<string, string>
     */
    private array $mergedCells = [];

    public function __construct(ExcelWriter $writer, array $columns, int $startCol, int $startRow)
    {
        $this->cols = $columns;
        $this->writer = $writer;
        $this->startCol = $startCol;
        $this->startRow = $startRow;
        self::initColIndexes($columns);
    }

    /**
     * It is faster than use Spreadsheet mergeCells(), but
     * no checks are made, must ensure:
     *
     * 1. no intersection ranges.
     * 2. cells in range must created
     */
    public function mergeCells(string $range): void
    {
        $this->mergedCells[$range] = $range;
    }

    /**
     * Reverse operation of @see mergeCells().
     */
    public function unmergeCells(string $range): void
    {
        unset($this->mergedCells[$range]);
    }

    /**
     * Save merged cells to current sheet.
     */
    public function flushMergedCells(): void
    {
        $this->getWriter()->getSheet()->setMergeCells($this->mergedCells);
    }

    public function getMergedCells(): array
    {
        return $this->mergedCells;
    }

    public function getWriter(): ExcelWriter
    {
        return $this->writer;
    }

    /**
     * Return column spread sheet index (start from 1)
     */
    public function getColumnIndex(ExcelColumn $col): int
    {
        return $this->colIdxes[spl_object_id($col)];
    }

    /**
     * Return column spread sheet name (start from 'A')
     */
    public function getColumnName(ExcelColumn $col): string
    {
        return $this->colNames[spl_object_id($col)];
    }

    /**
     * If column has colSpan, returns the end column name, or return getColumnName().
     */
    public function getColumnEndName(ExcelColumn $col): string
    {
        return $this->colEndNames[spl_object_id($col)];
    }

    /**
     * @return ExcelColumn[]
     */
    public function getColumns(): array
    {
        return $this->cols;
    }

    /**
     * Return row index (start from 1) of the first data row.
     */
    public function getStartDataRow(): int
    {
        return $this->startRow + 1;
    }

    /**
     * Return row index (start from 1) of the table first row,
     * i.e. first table head row.
     */
    public function getStartRow(): int
    {
        return $this->startRow;
    }

    public function getStartCol(): int
    {
        return $this->startCol;
    }

    /**
     * Return spread sheet row index (start from 1)
     */
    public function getRowIndex(): int
    {
        return $this->writer->getRow();
    }

    /**
     * @return mixed
     */
    public function getValue(array $row, ExcelColumn $column)
    {
        $idx = $this->getColumnIndex($column);

        return $row[$idx - $this->startCol];
    }

    /**
     * @return callable(array): mixed function get value from data row.
     * It is faster than @see getValue().
     */
    public function newValueGetter(ExcelColumn $column): callable
    {
        $idx = $this->getColumnIndex($column) - $this->startCol;

        return fn(array $row) => $row[$idx];
    }

    /**
     * @param ExcelColumn[] $cols
     * @return array<int, int>
     */
    private function initColIndexes(array $cols): void
    {
        [$idxes, $names, $endNames] = [[], [], []];
        $idx = $this->startCol;
        foreach ($cols as $col) {
            $id = spl_object_id($col);
            $idxes[$id] = $idx;
            $names[$id] = Coordinate::stringFromColumnIndex($idx);
            $idx += $col->getColSpan();
            $endNames[$id] = Coordinate::stringFromColumnIndex($idx - 1);
        }

        [$this->colIdxes, $this->colNames, $this->colEndNames] = [$idxes, $names, $endNames];
    }
}
