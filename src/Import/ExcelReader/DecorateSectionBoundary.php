<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader;

/**
 * Decorate exist section boundary, use it as super class.
 */
class DecorateSectionBoundary implements SectionBoundaryInterface
{
    private SectionBoundaryInterface $inner;

    public function __construct(SectionBoundaryInterface $inner)
    {
        $this->inner = $inner;
    }

    public function isSectionStart(ExcelReader $reader): bool
    {
        return $this->inner->isSectionStart($reader);
    }

    public function isSectionEnd(ExcelReader $reader): bool
    {
        return $this->inner->isSectionEnd($reader);
    }

    public function onReadComplete(ExcelReader $reader): void
    {
        $this->inner->onReadComplete($reader);
    }
}
