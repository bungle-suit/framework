<?php

declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader;

class ExcelLocation
{
    public function __construct(string $sheetName, int $row)
    {
        $this->sheetName = $sheetName;
        $this->row = $row;
    }

    private string $sheetName;

    public function getSheetName(): string
    {
        return $this->sheetName;
    }

    public function setSheetName(string $sheetName): void
    {
        $this->sheetName = $sheetName;
    }

    /**
     * Row index start from 1
     */
    private int $row;

    public function getRow(): int
    {
        return $this->row;
    }

    public function setRow(int $row): void
    {
        $this->row = $row;
    }

    public function __toString(): string
    {
        return '工作表"'.$this->sheetName.'"第'.$this->row.'行';
    }
}
