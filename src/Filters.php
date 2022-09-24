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
    public function afterThatTime(DateTime $time): callable
    {
        return function () use ($time) {
            return $this->basal->now() >= $time;
        };
    }
}
