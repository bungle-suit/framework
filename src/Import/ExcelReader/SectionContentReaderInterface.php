<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader;

interface SectionContentReaderInterface
{
    /**
     * Called on section start.
     */
    public function onSectionStart(ExcelReader $reader): void;

    /**
     * Read current excel line, it is okay to read multiple lines,
     * but better not, ExcelReader handles boundary start/end, skips
     * empty lines.
     */
    public function readRow(ExcelReader $reader): void;

    /**
     * Called on section end.
     */
    public function onSectionEnd(ExcelReader $reader): void;
}
