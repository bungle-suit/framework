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

    public function __construct(ExcelWriter $writer, array $columns, int $startCol, int $startRow)
    {
        $this->cols = $columns;
        $this->writer = $writer;
        $this->startCol = $startCol;
        $this->startRow = $startRow;
        $this->colIdxes = self::initColIndexes($columns);
        $this->colNames = self::initColNames($columns);
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
    private function initColIndexes(array $cols): array
    {
        $arr = [];
        $idx = $this->startCol;
        foreach ($cols as $col) {
            $arr[spl_object_id($col)] = $idx;
            $idx += $col->getColSpan();
        }

        return $arr;
    }

    /**
     * @param ExcelColumn[] $cols
     * @return array<int, string>
     */
    private function initColNames(array $cols): array
    {
        $arr = [];
        foreach ($cols as $col) {
            $id = spl_object_id($col);
            $arr[$id] = Coordinate::stringFromColumnIndex($this->getColumnIndex($col));
        }

        return $arr;
    }
}
