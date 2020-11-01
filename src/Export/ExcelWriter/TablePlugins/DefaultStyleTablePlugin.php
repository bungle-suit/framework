<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter\TablePlugins;

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
        $sheet->getStyleByColumnAndRow(
            $context->getStartCol(),
            $context->getRowIndex(),
            $context->getColumnIndex(FP::notNull(FP::last($context->getColumns()))),
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
        $sheet = $context->getWriter()->getSheet();
        $sheet
            ->getStyleByColumnAndRow(
                $context->getStartCol(),
                $context->getStartRow(),
                $context->getColumnIndex(FP::notNull(FP::last($context->getColumns()))),
                $context->getRowIndex() - 1,
            )
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
    }
}
