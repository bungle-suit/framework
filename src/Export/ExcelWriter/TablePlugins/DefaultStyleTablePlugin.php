<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter\TablePlugins;

use Bungle\Framework\Export\ExcelWriter\ExcelColumn;
use Bungle\Framework\Export\ExcelWriter\TableContext;
use Bungle\Framework\FP;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * @internal implement Default style of @see ExcelWriter::writeTable().
 */
class DefaultStyleTablePlugin extends AbstractTablePlugin
{
    public function onHeaderFinish(TableContext $context): void
    {
        $sheet = $context->getWriter()->getSheet();
        /** @var ExcelColumn $lastCol */
        $lastCol = FP::last($context->getColumns());
        $sheet->getStyleByColumnAndRow(
            $context->getStartCol(),
            $context->getRowIndex(),
            $context->getColumnIndex($lastCol),
            $context->getRowIndex()
        )->applyFromArray(
            [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFDDDDDD'],
                ],
            ]
        );
    }

    public function onTableFinish(TableContext $context): void
    {
        if (!($cols = $context->getColumns())) {
            return;
        }

        $sheet = $context->getWriter()->getSheet();
        $startCol = $context->getColumnName($cols[0]);
        /** @var ExcelColumn $lastCol */
        $lastCol = FP::last($cols);
        $endCol = $context->getColumnEndName($lastCol);
        $endRow = $context->getRowIndex() - 1;
        $sheet->getStyle("$startCol{$context->getStartRow()}:$endCol{$endRow}")
              ->getBorders()
              ->getAllBorders()
              ->setBorderStyle(Border::BORDER_THIN);
    }
}
