<?php

declare(strict_types=1);

namespace Bungle\Framework\Export;

use LogicException;
use RuntimeException;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Webmozart\Assert\Assert;
use ZipArchive;

class FS implements FSInterface
{
    private string $temDir;

    public function __construct(string $temDir = null)
    {
        $this->temDir = $temDir ?? sys_get_temp_dir();
    }

    public function tempFile(?string $content = null): string
    {
        if (($r = tempnam($this->temDir, 'bungle-')) === false) {
            throw new RuntimeException('Failed create tmpFile');
        }
        if ($content !== null) {
            ErrorHandler::call(fn() => file_put_contents($r, $content, LOCK_EX));
        }

        return $r;
    }

    public function removeFile(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function filesize(string $path): int
    {
        return ErrorHandler::call(fn() => filesize($path));
    }

    /**
     * Read content of a zip file entry. $path format: izip:///path/to/file.zip#entryIdx#entryName
     */
    private function readIZip(string $path): string
    {
        $words = explode('#', $path);
        if (count($words) < 2) {
            throw new RuntimeException("Invalid izip path: $path");
        }

        $zip = new ZipArchive();
        $p = substr($words[0], 7);
        $r = $zip->open($p);
        if ($r !== true) {
            throw new RuntimeException("Open zip file $p failed: $r");
        }

        $r = $zip->getFromIndex((int)$words[1]);
        $zip->close();

        if ($r === false) {
            throw new LogicException("failed to read ".$path);
        }

        return $r;
    }

    /** @inheritDoc */
    public function readFile(string $path, string|array $charset = ''): string
    {
        if (str_starts_with($path, 'izip:///')) {
            $r = $this->readIZip($path);
        } else {
            $r = file_get_contents($path);
        }

        if ($r === false) {
            throw new RuntimeException("Read file $path failed");
        }

        if (is_array($charset)) {
            $charset = mb_detect_encoding($r, $charset, true);
            Assert::notFalse($charset, 'Failed detect charset');
        }

        if ($charset !== '') {
            $r = mb_convert_encoding($r, 'UTF-8', $charset);
        }

        return $r;
    }

    /**
     * Create php stream from string.
     * @return resource
     */
    public static function stringStream(string $s)
    {
        $r = fopen('php://memory', 'r+');
        Assert::notFalse($r);
        Assert::notFalse(fwrite($r, $s));
        Assert::notFalse(rewind($r));

        return $r;
    }

    /**
     * Helps capture save to file/stream output to string.
     * @return array{resource, callable(): string}
     *
     * First return value is opened stream, after all write done, call 2nd return
     * value to get the content as string, and close the stream.
     */
    public static function writeToString(): array
    {
        $f = fopen('php://temp', 'r+');
        Assert::notFalse($f);

        $capture = function () use ($f) {
            Assert::notFalse(rewind($f));
            $r = stream_get_contents($f);
            Assert::notFalse($r);
            Assert::notFalse(fclose($f));

            return $r;
        };

        return [$f, $capture];
    }

    public function tempDir(): string
    {
        return sys_get_temp_dir();
    }
}
