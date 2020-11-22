<?php

declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader;

class ExcelLocation
{
    public function __construct(string $sheetName, int $row, ?string $col = null)
    {
        $this->sheetName = $sheetName;
        $this->row = $row;
        $this->col = $col;
    }

    private string $sheetName;

    public function getSheetName(): string
    {
        return $this->sheetName;
    }

    /**
     * Row index start from 1
     */
    private int $row;

    public function getRow(): int
    {
        return $this->row;
    }

    /**
     * Column start from 'A'
     */
    private ?string $col;

    public function getCol(): ?string
    {
        return $this->col;
    }

    public function __toString(): string
    {
        return $this->col === null ?
            "工作表\"{$this->sheetName}\"第{$this->row}行" :
            "工作表\"{$this->sheetName}\"单元格{$this->col}{$this->row}";
    }
}
