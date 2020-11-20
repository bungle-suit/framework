<?php

declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\TableReader;

use PhpOffice\PhpSpreadsheet\Cell\Cell;

class TextColumnHeaderCellMatcher implements ColumnHeaderCellMatcherInterface
{
    private string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    public function matches(Cell $cell): bool
    {
        return $cell->getValue() === $this->text;
    }
}
