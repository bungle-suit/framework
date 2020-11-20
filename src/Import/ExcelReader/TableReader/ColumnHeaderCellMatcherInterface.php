<?php

declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\TableReader;

use PhpOffice\PhpSpreadsheet\Cell\Cell;

interface ColumnHeaderCellMatcherInterface
{
    public function matches(Cell $cell): bool;
}
