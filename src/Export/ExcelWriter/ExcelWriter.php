<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter;

use Bungle\Framework\Export\ExcelWriter\TablePlugins\ColumnWidthTablePlugin;
use Bungle\Framework\Export\ExcelWriter\TablePlugins\CompositeTablePlugin;
use Bungle\Framework\Export\ExcelWriter\TablePlugins\DefaultStyleTablePlugin;
use Bungle\Framework\Export\ExcelWriter\TablePlugins\FormulaColumnTablePlugin;
use Bungle\Framework\Export\ExcelWriter\TablePlugins\NumberFormatTablePlugin;
use Bungle\Framework\Export\ExcelWriter\TablePlugins\SumTablePlugin;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ExcelWriter extends ExcelOperator
{
    /**
     * Create a new work sheet as current sheet, $name as its text/title.
     */
    public function newSheet(string $name): void
    {
        $this->sheet = $this->book->createSheet();
        $this->sheet->setTitle($name);
        $this->sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $this->row = 1;
    }

    public const TITLE_STYLE_H1 = 'h1';
    public const TITLE_STYLE_H6 = 'h6';

    /**
     * @param int $nCells how many cells to merge, 0 to disable
     */
    public function writeTitle(
        string $title,
        int $nCells = 0,
        string $col = 'A',
        string $headerStyle = self::TITLE_STYLE_H1
    ): void {
        $cellAddr = "{$col}{$this->row}";
        /** @var Cell $titleCell */
        $titleCell = $this->sheet->getCell($cellAddr);
        $titleCell->setValue($title);

        $titleStyles = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'font' => [
                'bold' => true,
            ],
        ];
        $titleCell->getStyle()->applyFromArray($titleStyles);
        if ($headerStyle === self::TITLE_STYLE_H1) {
            $titleCell->getStyle()->getFont()->setSize(16);
            $this->sheet->getRowDimension($this->row)->setRowHeight(20);
        }
        if ($nCells > 1) {
            $colIdx = Coordinate::columnIndexFromString($col);
            $endCol = Coordinate::stringFromColumnIndex($colIdx + $nCells - 1);
            $this->sheet->mergeCells("$col{$this->row}:{$endCol}{$this->row}");
        }

        $this->nextRow();
    }

    /**
     * @param ExcelColumn[] $cols ,
     * @param array<string, mixed> $options
     */
    private static function createPlugins(array $cols, array $options): TablePluginInterface
    {
        /** @var array<TablePluginInterface> $userPlugins */
        $userPlugins = $options['plugins'];
        foreach ($cols as $col) {
            if ($col->formulaEnabled()) {
                $userPlugins[] = new FormulaColumnTablePlugin();
                break;
            }
        }

        foreach ($cols as $col) {
            if ($col->isEnableSum()) {
                $userPlugins[] = new SumTablePlugin();
                break;
            }
        }

        foreach ($cols as $col) {
            if ($col->getCellFormat() !== null) {
                $userPlugins[] = new NumberFormatTablePlugin();
                break;
            }
        }

        if (!$options['disableDefaultStyle']) {
            $userPlugins[] = new DefaultStyleTablePlugin();
        }
        $userPlugins[] = new ColumnWidthTablePlugin();

        return new CompositeTablePlugin($userPlugins);
    }

    /**
     * @param array<int, ExcelColumn> $cols
     * @param iterable<object|(string|number|null)[]> $rows
     * @param array<string, mixed> $options
     */
    public function writeTable(
        array $cols,
        iterable $rows,
        string $col = 'A',
        array $options = []
    ): void {
        $options = self::resolveTableOptions($options);
        $plugin = self::createPlugins($cols, $options);

        $sheet = $this->sheet;
        $startRow = $this->row;
        $startColIdx = Coordinate::columnIndexFromString($col);

        $pluginContext = new TableContext($this, $cols, $startColIdx, $startRow);
        $plugin->onTableStart($pluginContext);

        /** @var ExcelColumn $c */
        foreach ($cols as $c) {
            $sheet->setCellValue(
                "{$pluginContext->getColumnName($c)}{$this->row}",
                $c->getHeader()
            );
            if ($c->getColSpan() > 1) {
                $sheet->mergeCells(
                    "{$pluginContext->getColumnName($c)}{$this->row}:{$pluginContext->getColumnEndName($c)}{$this->row}"
                );
            }
        }
        $plugin->onHeaderFinish($pluginContext);
        $this->nextRow();

        $propertyAccessor = new PropertyAccessor();
        foreach ($rows as $idx => $row) {
            $dataRow = [];
            /** @var ExcelColumn $c */
            foreach ($cols as $c) {
                [$colName, $colEndName] = [
                    $pluginContext->getColumnName($c),
                    $pluginContext->getColumnEndName($c),
                ];
                $v = $c->getPropertyPath() ?
                    $propertyAccessor->getValue($row, $c->getPropertyPath()) :
                    $row;
                $v = ($c->getValueConverter())($v, $idx, $row);
                $dataRow[] = $v;
                if ($c->getColSpan() > 1) {
                    for ($i = 0; $i < ($c->getColSpan() - 1); $i++) {
                        $dataRow[] = null;
                    }
                    $sheet->mergeCells("$colName{$this->row}:$colEndName{$this->row}");
                }
            }
            $sheet->fromArray($dataRow, null, "$col{$this->row}", true);
            $plugin->onRowFinish($dataRow, $pluginContext);

            $this->nextRow();
        }
        $plugin->onDataFinish($pluginContext);
        $plugin->onTableFinish($pluginContext);
    }

    /**
     * Write a grid area, grid is excel area with various cells for each row.
     * Write a border lines for each cell by default.
     *
     * @phpstan-param array<(ExcelCell|(string|number|null))[]> $cells
     */
    public function writeGrid(array $cells, string $col = 'A'): void
    {
        $startRow = $this->getRow();
        $startColIdx = Coordinate::columnIndexFromString($col);
        $maxGridCols = 0;
        foreach ($cells as $row) {
            $colIdx = $startColIdx;
            foreach ($row as $cv) {
                $c = $this->sheet->getCellByColumnAndRow($colIdx, $this->getRow());
                if (!($cv instanceof ExcelCell)) {
                    $c->setValue($cv);
                    $colIdx++;
                    continue;
                }

                $c->setValue($cv->value);
                if ($cv->span !== null) {
                    [$spanWidth, $spanHeight] = $cv->span;
                    assert($spanHeight === 1, 'Span more than one row not supported');
                    $r = $this->getRow();
                    $startCol = Coordinate::stringFromColumnIndex($colIdx);
                    $endCol = Coordinate::stringFromColumnIndex($colIdx + $spanWidth - 1);
                    $this->sheet->mergeCells("$startCol$r:$endCol$r");
                    $colIdx += $spanWidth;
                } else {
                    $colIdx++;
                }

                if ($formatCode = $cv->options[ExcelCell::OPTION_FORMAT_CODE] ?? null) {
                    $c->getStyle()->getNumberFormat()->setFormatCode($formatCode);
                }

                $maxGridCols = max($maxGridCols, $colIdx - $startColIdx);
            }
            $this->nextRow();
        }

        $startCell = $col.$startRow;
        $endCell = Coordinate::stringFromColumnIndex($startColIdx + $maxGridCols - 1).
            ($this->getRow() - 1);
        $this->sheet
            ->getStyle("{$startCell}:{$endCell}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
    }

    /**
     * Set current sheet column widths.
     *
     * @param array<int|float> $colWidths column width start from 'A' column.
     */
    public function setColumnWidths(array $colWidths): void
    {
        foreach ($colWidths as $idx => $width) {
            $col = $this->sheet->getColumnDimension(Coordinate::stringFromColumnIndex($idx + 1));
            $col->setWidth($width);
        }
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private static function resolveTableOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setDefault('plugins', [])
            ->setAllowedTypes('plugins', ['null', 'array', TablePluginInterface::class])
            ->setNormalizer(
                'plugins',
                function ($options, $val) {
                    if ($val === null) {
                        return [];
                    }

                    return is_array($val) ? $val : [$val];
                }
            );
        $resolver
            ->setDefault('disableDefaultStyle', false)
            ->setAllowedTypes('disableDefaultStyle', 'bool');

        return $resolver->resolve($options);
    }

    public static function parseMemoryLimit(): int
    {
        // parse php.ini memory_limit
        $memory_limit = ini_get('memory_limit');
        if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
            if ($matches[2] == 'G') {
                $memory_limit = $matches[1] * 1024 * 1024 * 1024; // nnnG -> nnn GB
            } elseif ($matches[2] == 'M') {
                $memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
            } elseif ($matches[2] == 'K') {
                $memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
            }
        }

        return intval($memory_limit);
    }
}
