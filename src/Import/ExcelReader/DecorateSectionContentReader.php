<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader;

/**
 * Decorate SectionContentReaderInterface, derive from it to provide some features.
 */
class DecorateSectionContentReader implements SectionContentReaderInterface
{
    private SectionContentReaderInterface $inner;

    public function __construct(SectionContentReaderInterface $inner)
    {
        $this->inner = $inner;
    }

    public function onSectionStart(ExcelReader $reader): void
    {
        $this->inner->onSectionStart($reader);
    }

    public function readRow(ExcelReader $reader): void
    {
        $this->inner->readRow($reader);
    }

    public function onSectionEnd(ExcelReader $reader): void
    {
        $this->inner->onSectionEnd($reader);
    }
}
