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
     * Delete the file at $path if exist.
     */
    public function removeFile(string $path): void;

    public function filesize(string $path): int;

    /**
     * Read file content.
     *
     * @param string $charset convert file content from $charset to UTF-8 before return,
     * if specified.
     */
    public function readFile(string $path, string $charset = ''): string;
}
