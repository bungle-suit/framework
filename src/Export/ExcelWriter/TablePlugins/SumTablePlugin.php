<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter\TablePlugins;

use Bungle\Framework\Export\ExcelWriter\TableContext;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * @internal used to implement ExcelColumn summary feature.
 */
class SumTablePlugin extends AbstractTablePlugin
{
    public function onTableFinish(TableContext $context): void
    {
        $sheet = $context->getWriter()->getSheet();
        $row = $context->getRowIndex();
        $firstSumCol = -1;
        foreach ($context->getColumns() as $idx => $col) {
            if ($col->isEnableSum()) {
                $firstSumCol = -1 === $firstSumCol ? $idx : $firstSumCol;
                $colName = $context->getColumnName($col);
                [$firstDataRow, $lastDataRow] = [$context->getStartDataRow(), $row - 1];
                /** @var Cell $c */
                $c = $sheet->getCell("{$colName}{$row}");
                $c->setValue("=round(sum({$colName}{$firstDataRow}:{$colName}{$lastDataRow}),2)");
            }
        }

        if ($firstSumCol > 0) {
            $colName = Coordinate::stringFromColumnIndex($context->getStartCol() + $firstSumCol - 1);
            /** @var Cell $c */
            $c = $sheet->getCell($colName.$row);
            $c->setValue('总计');
            $c->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
    }
}
