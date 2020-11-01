<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter;

use Bungle\Framework\Export\ExcelWriter\TablePlugins\AbstractTablePlugin;

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
     * Called after written header row before move to next row.
     */
    public function onHeaderFinish(TableContext $context): void;

    /**
     * Called after row write to spread sheet.
     * @param mixed[] $rows data just written, indexed from 0,
     * null value exists because of column span. Use @see TableContext::getValue()
     * to get cell value by column.
     */
    public function onRowFinish(array $rows, TableContext $context): void;

    /**
     * Called after the last data row.
     */
    public function onDataFinish(TableContext $context): void;

    /**
     * Called after write the last line, current row move to next line of last data row.
     */
    public function onTableFinish(TableContext $context): void;
}
