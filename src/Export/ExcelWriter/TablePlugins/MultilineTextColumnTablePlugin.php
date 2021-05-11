<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter\TablePlugins;

use Bungle\Framework\Export\ExcelWriter\TableContext;

/**
 * MultilineTextColumnTablePlugin not enabled by default, add it into writeTable()
 * plugins list, and set the column may contains '\n' with option OPT_ENABLE_MULTI_LINE to true.
 */
class MultilineTextColumnTablePlugin extends AbstractTablePlugin
{
    public const OPT_ENABLE_MULTI_LINE = 'multi_line_text';

    public function onTableFinish(TableContext $context): void
    {
        $sheet = $context->getWriter()->getSheet();
        $lastRow = $context->getRowIndex() - 1;
        foreach ($context->getColumns() as $c) {
            if ($c->getOptions()[self::OPT_ENABLE_MULTI_LINE] ?? false) {
                $sheet
                    ->getStyleByColumnAndRow(
                        $context->getColumnIndex($c),
                        $context->getStartRow(),
                        $context->getColumnIndex($c),
                        $lastRow,
                    )
                    ->getAlignment()->setWrapText(true);
            }
        }
    }
}
