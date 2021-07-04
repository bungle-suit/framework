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

    /**
     * @param SectionReader[] $sections
     */
    public function setupSections(array $sections): void
    {
        $this->sections = $sections;
    }

    private function doRead(array $sectionReaders): void
    {
        $sheet = $this->getSheet();
        $curSection = null;
        $emptyRows = 0;
        $fIsEmptyRow = SectionBoundary::isEmptyRow();
        for ($maxRow = $sheet->getHighestDataRow(); $this->row <= $maxRow; $this->nextRow()) {
            if ($fIsEmptyRow($this)) {
                if (++$emptyRows >= 30) {
                    break;
                }
            } else {
                $emptyRows = 0;
            }

            if ($curSection === null) {
                foreach ($sectionReaders as $section) {
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

        foreach ($sectionReaders as $section) {
            $section->getBoundary()->onReadComplete($this);
        }
    }

    public function read(): void
    {
        $this->doRead($this->sections);
    }

    /**
     * Return current location.
     */
    public function getLocation(?string $col = null): ExcelLocation
    {
        return new ExcelLocation($this->getSheet()->getTitle(), $this->row, $col);
    }

    /**
     * @return null|array{int, int} returns section start/end row no.
     */
    public function resolveSectionBoundary(SectionBoundaryInterface $boundary): ?array
    {
        $sections = [new SectionReader('', $boundary, $boundaryReader = new SectionBoundaryContentReader())];
        $this->doRead($sections);
        if (!$boundaryReader->isHit()) {
            return null;
        }

        return [$boundaryReader->getStartRow(), $boundaryReader->getEndRow()];
    }
}
