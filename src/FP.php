<?php
declare(strict_types=1);

namespace Bungle\Framework;

/**
 * Common functional program functions
 */
class FP
{
    /**
     * Return a function to get specific attribute from object.
     */
    public static function attr(string $name): callable
    {
        return fn (object $o) => $o->$name;
    }
}
