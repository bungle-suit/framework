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
     * @param ?string $content fill the created file with $content if not null.
     */
    public function tempFile(?string $content = null): string;

    /**
     * Delete the file at $path if exist.
     */
    public function removeFile(string $path): void;

    public function filesize(string $path): int;

    /**
     * Read file content.
     *
     * @param string|string[] $charset convert file content from $charset to UTF-8 before return,
     * if specified. If array it is charset list, readFile will test file content use mb_detect_encoding().
     */
    public function readFile(string $path, string|array $charset = ''): string;

    /**
     * Return system temp directory, alias of sys_get_temp_dir()
     */
    public function tempDir(): string;
}
