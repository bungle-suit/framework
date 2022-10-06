<?php

declare(strict_types=1);

namespace Bungle\Framework;

use Bungle\Framework\Ent\BasalInfoService;
use DateTime;

class Filters
{
    public function __construct(private BasalInfoService $basal)
    {
    }

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

    /**
     * @return callable(): bool Return true if now equal or after $time
     */
    public function afterThatTime(DateTime $time, callable $fNow = null): callable
    {
        $fNow = $fNow ?? $this->basal->now(...);

        return function () use ($fNow, $time) {
            return $fNow() >= $time;
        };
    }
}
