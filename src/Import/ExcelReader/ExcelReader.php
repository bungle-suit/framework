<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader;

use Bungle\Framework\Export\ExcelWriter\ExcelOperator;
use RuntimeException;

/**
 * ExcelReader made by SectionReaderInterface,
 *
 * A section reader has two parts:
 *
 * SectionBoundaryInterface, detect section start/stop.
 * SectionContentReaderInterface do the actually reading, such as LabelledReader,
 * TableReader
 */
class ExcelReader extends ExcelOperator
{
    /**
     * Switch current work sheet, reset current row counter.
     *
     * @param bool $allowNotExist
     * @return bool returns true if sheet exist, and switch to it successfully.
     * If $allowNotExist is false, always returns tree.
     * @throws RuntimeException if $allowNotExist is false, and worksheet not exist.
     */
    public function switchWorksheet(string $sheetName, bool $allowNotExist = false): bool
    {
        $sheet = $this->book->getSheetByName($sheetName);
        if ($sheet === null) {
            if ($allowNotExist) {
                return false;
            }
            throw new RuntimeException("找不到工作表: $sheetName");
        }
        $this->sheet = $sheet;
        $this->row = 1;
        return true;
    }
}
