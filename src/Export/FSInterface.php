<?php
declare(strict_types=1);

namespace Bungle\Framework\Export;

/**
 * Interface for Exporter to isolate physical file system.
 */
interface FSInterface
{
    /**
     * Create a temp file, returns temp file name.
     *
     * @param string $prefix prefix of the newly created filename.
     * @param ?string $content fill the created file with $content if not null.
     */
    public function createTempFile(string $prefix, ?string $content = null): string;

    /**
     * Delete the file at $path.
     */
    public function removeFile(string $path): void;
}
