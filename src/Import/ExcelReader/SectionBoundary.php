<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * Callback based SectionBoundaryInterface
 */
class SectionBoundary implements SectionBoundaryInterface
{
    private $isSectionStart;
    private $isSectionEnd;

    public function __construct(callable $isSectionStart, callable $isSectionEnd)
    {
        $this->isSectionStart = $isSectionStart;
        $this->isSectionEnd = $isSectionEnd;
    }

    public function isSectionStart(ExcelReader $reader): bool
    {
        return ($this->isSectionStart)($reader);
    }

    public function isSectionEnd(ExcelReader $reader): bool
    {
        return ($this->isSectionEnd)($reader);
    }

    public function onReadComplete(ExcelReader $reader): void
    {
    }

    /**
     * Shortcut of `new MatchFirstBoundary($boundary)`
     */
    public static function matchFirst(SectionBoundaryInterface $boundary): SectionBoundaryInterface
    {
        return new MatchFirstSectionBoundary($boundary);
    }

    /**
     * Returns callable that returns true if current sheet name is one of $sheetNames
     *
     * @return callable(ExcelReader): bool
     */
    public static function sheetNameIs(string ...$sheetNames): callable
    {
        return function (ExcelReader $reader) use ($sheetNames): bool {
            return in_array($reader->getSheet()->getTitle(), $sheetNames);
        };
    }

    /**
     * Section start detect function, matched if one of $keywords exist in $colIdx cell.
     *
     * @param string[] $keywords
     * @param string $col Which column to read, such as 'A' means first column.
     * @return callable(ExcelReader): bool
     */
    public static function colIs(array $keywords, string $colIdx = 'A'): callable
    {
        return fn(ExcelReader $reader): bool => in_array($reader->getCellValue($colIdx.$reader->getRow()), $keywords);
    }

    /**
     * Section start detect function, matched if current row index > $rowIdx.
     *
     * @return callable(ExcelReader): bool
     */
    public static function rowAfter(int $rowIdx): callable
    {
        return fn (ExcelReader $reader): bool => $reader->getRow() > $rowIdx;
    }

    /**
     * Returns function tells that current row is empty row, by looking up
     * first $colDetects cells is empty.
     *
     * @param int $colDetects
     */
    public static function isEmptyRow(int $colDetects = 10): callable
    {
        return function (ExcelReader $reader) use ($colDetects): bool {
            for ($i = 1; $i <= $colDetects; $i++) {
                $addr = Coordinate::stringFromColumnIndex($i).$reader->getRow();
                $v = $reader->getCellValue($addr);
                if ($v !== null && $v !== '') {
                    return false;
                }
            }
            return true;
        };
    }
}
