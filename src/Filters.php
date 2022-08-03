<?php

declare(strict_types=1);

namespace Bungle\Framework;

class Filters
{
    /**
     * @template T
     * @param class-string<T> $type
     * @return callable(object):bool
     */
    public static function isInstanceOf(string $type): callable
    {
        return function ($value) use ($type) {
            return $value instanceof $type;
        };
    }
}
