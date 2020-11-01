<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter;

/**
 * TablePluginInterface may add more methods afterwords, derives
 * from @see AbstractTablePlugin to survise design changes.
 */
interface TablePluginInterface
{
    /**
     * Called before write table header.
     */
    public function onTableStart(TableContext $context): void;

    /**
     * Called after row write to spread sheet.
     * @param mixed[] $rows data just written, indexed from 0,
     * null value exists because of column span. Use @see TableContext::getValue()
     * to get cell value by column.
     */
    public function onRowFinish(array $rows, TableContext $context): void;
}
