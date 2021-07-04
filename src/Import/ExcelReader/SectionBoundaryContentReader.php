<?php

declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader;

use LogicException;

/**
 * Do nothing but report section start/end row no.
 */
class SectionBoundaryContentReader implements SectionContentReaderInterface
{
    private int $start = -1;
    private int $end = -1;

    public function onSectionStart(ExcelReader $reader): void
    {
        $this->start = $reader->getRow();
    }

    public function readRow(ExcelReader $reader): void
    {
    }

    public function onSectionEnd(ExcelReader $reader): void
    {
        $this->end = $reader->getRow();
    }

    public function isHit(): bool
    {
        return $this->start !== -1;
    }

    /**
     * @throws LogicException if section never hit, always call self::isHit() first.
     */
    public function getStartRow(): int
    {
        return $this->start;
    }

    /**
     * Returns -1 if end row not detected.
     * @throws LogicException if section never hit, always call self::isHit() first.
     */
    public function getEndRow(): int
    {
        return $this->end;
    }
}