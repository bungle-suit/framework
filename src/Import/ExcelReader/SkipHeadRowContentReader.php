<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader;

/**
 * Wrap inner reader, skip head $n rows from read.
 */
class SkipHeadRowContentReader extends DecorateSectionContentReader
{
    private int $n;
    private int $startRow;
    private bool $first;

    public function __construct(SectionContentReaderInterface $inner, int $n = 1)
    {
        parent::__construct($inner);

        $this->n = $n;
    }

    public function onSectionStart(ExcelReader $reader): void
    {
        $this->first = true;
        $this->startRow = $reader->getRow();
    }

    public function readRow(ExcelReader $reader): void
    {
        if (($reader->getRow() - $this->startRow) >= $this->n) {
            if ($this->first) {
                $this->first = false;
                parent::onSectionStart($reader);
            }
            parent::readRow($reader);
        }
    }
}
