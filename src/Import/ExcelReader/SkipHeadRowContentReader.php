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

    public function __construct(SectionContentReaderInterface $inner, int $n = 1)
    {
        parent::__construct($inner);

        $this->n = $n;
    }

    public function onSectionStart(ExcelReader $reader): void
    {
        $this->startRow = $reader->getRow();
        parent::onSectionStart($reader);
    }

    public function readRow(ExcelReader $reader): void
    {
        if (($reader->getRow() - $this->startRow) >= $this->n) {
            parent::readRow($reader);
        }
    }
}
