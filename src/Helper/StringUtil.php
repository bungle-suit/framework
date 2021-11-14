<?php

declare(strict_types=1);

namespace Bungle\Framework\Helper;

use Bungle\Framework\FP;

class StringUtil
{
    /**
     * Return true if $s contains one of $keywords
     * @param string[] $keywords
     */
    public static function containsAny(string $s, array $keywords): bool
    {
        return FP::any(fn($x) => str_contains($s, $x), $keywords);
    }

    /**
     * @return callable(string): bool
     */
    public static function newContainsAny(array $keywords): callable
    {
        return fn(string $s) => self::containsAny($s, $keywords);
    }
}
