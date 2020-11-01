<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter\TablePlugins;

use Bungle\Framework\Export\ExcelWriter\ExcelColumn;
use Bungle\Framework\Export\ExcelWriter\TableContext;

/**
 * Merge column cells
 */
class CellMergeTablePlugin extends AbstractTablePlugin
{
    private array $cols;
    private array $startRow;
    private array $groupData;
    /** @var callable(mixed): mixed */
    private array $dataAccessors;

    /**
     * @param ExcelColumn[] $cols
     */
    public function __construct(array $cols)
    {
        $this->cols = $cols;
    }

    public function onTableStart(TableContext $context): void
    {
        $this->dataAccessors = [];
        foreach ($this->cols as $col) {
            $this->dataAccessors[] = $context->newValueGetter($col);
        }
    }

    public function onRowFinish(array $row, TableContext $context): void
    {
        $groupData = $this->getGroupData($row);

        if (!isset($this->groupData)) {
            $this->groupData = $groupData;
            $this->startRow = array_fill(0, count($this->cols), $context->getRowIndex());

            return;
        }

        foreach ($this->groupData as $idx => $v) {
            if ($groupData[$idx] != $this->groupData[$idx]) {
                for ($i = $idx; $i < count($this->cols); $i++) {
                    $this->onNewGroup($i, $context->getRowIndex(), $context);
                    $this->groupData[$i] = $groupData[$i];
                    $this->startRow[$i] = $context->getRowIndex();
                }
                break;
            }
        }
    }

    public function onDataFinish(TableContext $context): void
    {
        if (!isset($this->groupData)) {
            return;
        }

        for ($idx = 0; $idx < count($this->cols); $idx++) {
            $this->onNewGroup($idx, $context->getRowIndex(), $context);
        }
    }

    private function onNewGroup(int $idx, int $curRow, TableContext $context): void
    {
        [$startRow, $c] = [$this->startRow[$idx], $this->cols[$idx]];
        if ($curRow - $startRow > 1) {
            $startCol = $context->getColumnIndex($c);
            $endCol = $startCol + $c->getColSpan() - 1;
            $sheet = $context->getWriter()->getSheet();
            if ($c->getColSpan() > 1) {
                for ($row = $startRow; $row < $curRow; $row++) {
                    $sheet->unmergeCellsByColumnAndRow($startCol, $row, $endCol, $row);
                }
            }
            $sheet->mergeCellsByColumnAndRow($startCol, $startRow, $endCol, $curRow - 1);
        }
    }

    /**
     * @param mixed[] $row
     * @return mixed[] return the grouped columns data
     */
    private function getGroupData(array $row): array
    {
        $r = [];
        foreach ($this->dataAccessors as $acc) {
            $r[] = $acc($row);
        }

        return $r;
    }
}
