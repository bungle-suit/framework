<?php

declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\TableReader;

use PhpOffice\PhpSpreadsheet\Cell\Cell;

class PatternColumnHeaderCellMatcher implements ColumnHeaderCellMatcherInterface
{
    private string $pattern;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public function matches(Cell $cell): bool
    {
        $text = $cell->getValue();
        $r = preg_match($this->pattern, $text);
        assert($r !== false, "Wrong pattern; {$this->pattern}");
        return $r === 1;
    }
}
