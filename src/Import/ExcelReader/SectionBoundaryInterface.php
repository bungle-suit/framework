<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader;

/**
 * Detects section starts/stops.
 */
interface SectionBoundaryInterface
{
    /**
     * Return true if current row is section start.
     */
    public function isSectionStart(ExcelReader $reader): bool;

    /**
     * Return true if current row is section end.
     */
    public function isSectionEnd(ExcelReader $reader): bool;

    /**
     * Called on read process complete.
     */
    public function onReadComplete(ExcelReader $reader): void;
}
