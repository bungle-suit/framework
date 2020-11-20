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
    /** @var SectionReader[] */
    private array $sections;

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

    /**
     * @param SectionReader[] $sections
     */
    public function setupSections(array $sections): void
    {
        $this->sections = $sections;
    }

    public function read(): void
    {
        $sheet = $this->getSheet();
        $curSection = null;
        $emptyRows = 0;
        $fIsEmptyRow = SectionBoundary::isEmptyRow();
        for ($maxRow = $sheet->getHighestDataRow(); $this->row <= $maxRow; $this->nextRow()) {
            if ($fIsEmptyRow($this)) {
                if (++$emptyRows >= 10) {
                    break;
                }
            } else {
                $emptyRows = 0;
            }

            if ($curSection === null) {
                foreach ($this->sections as $section) {
                    if ($section->getBoundary()->isSectionStart($this)) {
                        $curSection = $section;
                        $curSection->getContentReader()->onSectionStart($this);
                        $curSection->getContentReader()->readRow($this);
                        break;
                    }
                }
                continue;
            }

            if ($curSection->getBoundary()->isSectionEnd($this)) {
                $curSection->getContentReader()->onSectionEnd($this);
                $curSection = null;
                $this->nextRow(-1);
                continue;
            }

            if (!($curSection->getIsEmptyRow())($this)) {
                $curSection->getContentReader()->readRow($this);
            }
        }
        $this->nextRow(-1); // fix row to last data row.

        if ($curSection !== null) {
            $curSection->getContentReader()->onSectionEnd($this);
            $curSection = null;
        }

        foreach ($this->sections as $section) {
            $section->getBoundary()->onReadComplete($this);
        }
    }
}
