<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter;

use Bungle\Framework\Import\ExcelReader\ExcelReader;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

/**
 * Base class of ExcelReader, ExcelWriter.
 */
class ExcelOperator
{
    protected Spreadsheet $book;
    protected Worksheet $sheet;
    protected int $row = 1;

    /**
     * Use the active worksheet as current worksheet.
     */
    public function __construct(Spreadsheet $book, Worksheet $sheet = null)
    {
        $this->book = $book;
        $this->sheet = $sheet ?? $book->getActiveSheet();
    }

    public static function getCellWidth(ExcelReader $reader, int $col): int
    {
        $sheet = $reader->getSheet();
        $cell = $sheet->getCellByColumnAndRow($col, $reader->getRow(), false);
        if ($cell === null) {
            return 1;
        }

        if (($range = $cell->getMergeRange()) === false) {
            return 1;
        }

        return Coordinate::rangeDimension($range)[0];
    }

    /**
     * @return int current row no start from 1
     */
    public function getRow(): int
    {
        return $this->row;
    }

    /**
     * Goto specific $row.
     */
    public function setRow(int $row): void
    {
        $this->row = $row;
    }

    /**
     * Goto next row.
     */
    public function nextRow(int $n = 1): void
    {
        $this->row += $n;
    }

    public function getSheet(): Worksheet
    {
        return $this->sheet;
    }

    public function getBook(): Spreadsheet
    {
        return $this->book;
    }

    /**
     * Return the value of cell at $loc.
     * If cell not exist, returns null, and not auto created.
     *
     * @return mixed
     */
    public function getCellValue(string $loc)
    {
        $cell = $this->sheet->getCell($loc, false);

        return self::cellValue($cell);
    }

    /**
     * @return mixed
     */
    public function getCellValueByColumn(int $col)
    {
        $cell = $this->sheet->getCellByColumnAndRow($col, $this->row, false);

        return self::cellValue($cell);
    }

    /**
     * @return mixed
     */
    private static function cellValue(?Cell $cell)
    {
        return $cell === null ? null : $cell->getCalculatedValue();
    }

    /**
     * @param mixed $v
     */
    public function setCellValue(int $col, $v): void
    {
        $cell = $this->sheet->getCellByColumnAndRow($col, $this->row, true);
        assert($cell !== null);
        $cell->setValue($v);
    }

    /**
     * @return bool true if sheet exist
     */
    public function switchOrCreateWorksheet(string $sheetName): bool
    {
        $r = $this->book->getSheetByName($sheetName);
        $this->row = 1;
        if ($r === null) {
            $r = $this->book->createSheet();
            $r->setTitle($sheetName);
            $r->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
            $this->sheet = $r;

            return false;
        }
        $this->sheet = $r;

        return true;
    }

    /**
     * Switch current work sheet, reset current row counter.
     *
     * @param string|string[] $sheetName try all names before return or raise exception.
     * @param bool $allowNotExist
     * @return bool returns true if sheet exist, and switch to it successfully.
     * If $allowNotExist is false, always returns tree.
     * @throws RuntimeException if $allowNotExist is false, and worksheet not exist.
     */
    public function switchWorksheet($sheetName, bool $allowNotExist = false): bool
    {
        $sheetNames = is_array($sheetName) ? $sheetName : [$sheetName];
        foreach ($sheetNames as $name) {
            $sheet = $this->book->getSheetByName($name);
            if ($sheet !== null) {
                $this->sheet = $sheet;
                $this->row = 1;

                return true;
            }
        }

        if ($allowNotExist) {
            return false;
        }
        $names = implode(', ', $sheetNames);
        throw new RuntimeException("找不到工作表: $names");
    }
}
