<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter\TablePlugins;

use Bungle\Framework\Export\ExcelWriter\TableContext;

/**
 * @internal to set column cell format.
 */
class NumberFormatTablePlugin extends AbstractTablePlugin
{
    public function onTableFinish(TableContext $context): void
    {
        $sheet = $context->getWriter()->getSheet();
        $lastRow = $context->getRowIndex() - 1;
        foreach ($context->getColumns() as $idx => $c) {
            if (($fmt = $c->getCellFormat()) !== null) {
                $sheet
                    ->getStyleByColumnAndRow(
                        $context->getColumnIndex($c),
                        $context->getStartRow(),
                        $context->getColumnIndex($c),
                        $lastRow,
                    )
                    ->getNumberFormat()->setFormatCode($fmt);
            }
        }
    }
}
