<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter\TablePlugins;

use Bungle\Framework\Export\ExcelWriter\ExcelColumn;
use Bungle\Framework\Export\ExcelWriter\TableContext;

/**
 * @internal used to write ExcelColumn formula.
 */
class FormulaColumnTablePlugin extends AbstractTablePlugin
{
    /**
     * @var ExcelColumn[] formula columns
     */
    private array $cols;

    public function onTableStart(TableContext $context): void
    {
        $this->cols = array_filter(
            $context->getColumns(),
            fn(ExcelColumn $col) => $col->formulaEnabled()
        );
    }

    public function onRowFinish(array $rows, TableContext $context): void
    {
        foreach ($this->cols as $col) {
            if ($col->formulaEnabled()) {
                $context->getWriter()->getSheet()->setCellValueByColumnAndRow(
                    $context->getColumnIndex($col),
                    $context->getRowIndex(),
                    ($col->getFormula())($context->getRowIndex(), $col, $context),
                );
            }
        }
    }
}
