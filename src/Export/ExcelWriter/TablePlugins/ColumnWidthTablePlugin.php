<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter\TablePlugins;

use Bungle\Framework\Export\ExcelWriter\ExcelColumn;
use Bungle\Framework\Export\ExcelWriter\TableContext;

class ColumnWidthTablePlugin extends AbstractTablePlugin
{
    public function onTableFinish(TableContext $context): void
    {
        foreach ($context->getColumns() as $col) {
            if (($w = $col->getOptions()[ExcelColumn::OPT_WIDTH] ?? null)) {
                $colName = $context->getColumnName($col);
                $context->getWriter()->getSheet()->getColumnDimension($colName)->setWidth($w);
            }
        }
    }
}
