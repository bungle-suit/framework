<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter\TablePlugins;

use Bungle\Framework\Export\ExcelWriter\TableContext;
use SplObjectStorage;

/**
 * MultilineTextColumnTablePlugin not enabled by default, add it into writeTable()
 * plugins list, and set the column may contains '\n' with option OPT_ENABLE_MULTI_LINE to true.
 */
class MultilineTextColumnTablePlugin extends AbstractTablePlugin
{
    public const OPT_ENABLE_MULTI_LINE = 'multi_line_text';

    private SplObjectStorage $dataAccessors;

    public function onTableStart(TableContext $context): void
    {
        $this->dataAccessors = new SplObjectStorage();
        foreach ($context->getColumns() as $c) {
            if ($c->getOptions()[self::OPT_ENABLE_MULTI_LINE] ?? false) {
                $this->dataAccessors[$c] = $context->newValueGetter($c);
            }
        }
    }

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

    public function onRowFinish(array $rows, TableContext $context): void
    {
        foreach ($context->getColumns() as $c) {
            if ($c->getOptions()[self::OPT_ENABLE_MULTI_LINE] ?? false) {
                $text = ($this->dataAccessors[$c])($rows);
                if (!is_string($text)) {
                    $lines = 1;
                } else {
                    $lines = max(1, mb_substr_count($text, "\n"));
                }

                if ($lines > 1) {
                    $context
                        ->getWriter()
                        ->getSheet()
                        ->getRowDimension($context->getRowIndex())
                        ->setRowHeight(12.75 * $lines);
                }
            }
        }
    }
}
