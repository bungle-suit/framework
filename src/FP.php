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

    /**
     * Returns true if iterable is empty.
     */
    public static function isEmpty(iterable $values): bool
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($values as $v) {
            return false;
        }
        return true;
    }

    /**
     * Function that returns argument.
     *
     * @param mixed $v
     * @returns mixed
     */
    public static function identity($v)
    {
        return $v;
    }

    /**
     * Function always returns zero
     */
    public static function zero(): int
    {
        return 0;
    }

    /**
     * Returns function that always returns specific value.
     * @param mixed $v
     */
    public static function constant($v): callable
    {
        return fn() => $v;
    }

    /**
     * Call init function to init variable if not set.
     * @param $fIsUninitialized callback accept the value to tell the value is unintialized, by default
     * use `isEmpty().
     *
     * @return mixed returns &v
     */
    public static function initVariable(&$v, callable $fInit, callable $fIsUninitialized = null)
    {
        if ($fIsUninitialized === null ? empty($v) : $fIsUninitialized($v)) {
            $v = $fInit();
        }
        return $v;
    }

    /**
     * If property not initialized, call $fInit to init the property.
     *
     * @param callable $fIsUninitialized test does the property initialized.
     *
     * If the property not set (isset() returns false), always init the property, ignores $fIsUninitialized.
     *
     * @return mixed the property value
     */
    public static function initProperty(object $o, string $property, callable $fInit, callable $fIsUninitialized = null)
    {
        if (!isset($o->$property) || ($fIsUninitialized !== null && $fIsUninitialized($o->$property))) {
            $o->$property = $fInit();
        }
        return $o->$property;
    }

    /**
     * If array item at specific $idx not initialized, call $fInit to init the array item.
     *
     * @param callable $fIsUninitialized test does the array item initialized.
     *
     * If the property not set (isset() returns false), always init the property, ignores $fIsUninitialized.
     *
     * @return mixed the array item value.
     */
    public static function initArrayItem(array &$arr, $idx, callable $fInit, callable $fIsUninitialized = null)
    {
        if (!isset($arr[$idx]) || ($fIsUninitialized !== null && $fIsUninitialized($arr[$idx]))) {
            $arr[$idx] = $fInit();
        }
        return $arr[$idx] = $fInit();
    }
}

