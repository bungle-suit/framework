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

    /**
     * @param string $method getter method name, must use full name, such as 'getName', 'hasRole'.
     * @return callable call specific getter function from object.
     */
    public static function getter(string $method): callable
    {
        return fn (object $o) => $o->$method();
    }

    /**
     * @return callable that always returns true.
     */
    public static function t(): callable
    {
        return fn() => true;
    }

    /**
     * @return callable that always returns false.
     */
    public static function f(): callable
    {
        return fn() => false;
    }

    /**
     * Group array/iterator, key returned by $fKey.
     */
    public static function group(callable $fKey, iterable $values): array {
        $r = [];
        foreach ($values as $value) {
            $key = $fKey($value);
            $r[$key][] = $value;
        }
        return $r;
    }

    /**
     * Call $fCheck on item of $values, returns true if any callback result is true.
     * Returns false if $values is empty.
     */
    public static function any(callable $fCheck, iterable $values): bool
    {
        foreach ($values as $value) {
            if ($fCheck($value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Call fCheck on item of $values, returns false if any callback result is false.
     * Return true if $values is empty.
     */
    public static function all(callable $fCheck, iterable $values): bool
    {
        foreach ($values as $value) {
            if (!$fCheck($value)) {
                return false;
            }
        }
        return true;
    }
}

