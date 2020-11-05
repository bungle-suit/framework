<?php

declare(strict_types=1);

namespace Bungle\Framework\Helper;

use RuntimeException;
use Symfony\Component\Uid\Uuid;

class FileHelper
{
    /**
     * Create hashed path from filename, 'abc.txt' -> 'a/b/abc.txt'.
     * @param int $hashLevel hashed directory levels.
     * @phpstan-param callable(): string $fFilename returns the filename. Use uuid by default.
     */
    public static function newHashedFilename(int $hashLevel = 2, callable $fFilename = null): string
    {
        $fFilename = $fFilename ?? [self::class, 'newRandomName'];
        $fn = $fFilename();
        $fnWithoutExt = pathinfo($fn, PATHINFO_FILENAME);
        if (strlen($fnWithoutExt) < $hashLevel) {
            throw new RuntimeException("count of $fnWithoutExt less than hash level $hashLevel");
        }

        $parts = [];
        for ($i = 0; $i < $hashLevel; $i++) {
            $parts[] = $fnWithoutExt[$i];
        }
        $parts[] = $fn;
        return implode(DIRECTORY_SEPARATOR, $parts);
    }

    private static function newRandomName(): string
    {
       return Uuid::v1()->toRfc4122();
    }
}
