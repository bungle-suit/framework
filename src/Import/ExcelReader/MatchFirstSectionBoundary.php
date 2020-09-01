<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader;

/**
 * Match section start on inner section boundary first matches,
 * useful to limit a section only occurred once.
 */
class MatchFirstSectionBoundary extends DecorateSectionBoundary
{
    private bool $hit = false;

    public function isSectionStart(ExcelReader $reader): bool
    {
        if ($this->hit) {
            return false;
        }

        return $this->hit = parent::isSectionStart($reader);
    }
}
