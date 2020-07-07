<?php
declare(strict_types=1);

namespace Bungle\Framework;

use LogicException;

/**
 * Common functional program functions
 */
class FP
{
    /**
     * Return a function to get a specific attribute from an object.
     */
    public static function attr(string $name): callable
    {
        return fn (object $o) => $o->$name;
    }

    /**
     * @param string $method getter method name, must use full name, such as 'getName', 'hasRole'.
     * @return callable call specific getter function from an object.
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
     *
     * @phpstan-template T
     * @phpstan-template K
     * @phpstan-param callable(T): K $fKey
     * @phpstan-param iterable<T> $values
     * @phpstan-return array<K, T[]>
     */
    public static function group(callable $fKey, iterable $values): array
    {
        $r = [];
        foreach ($values as $value) {
            $key = $fKey($value);
            $r[$key][] = $value;
        }
        return $r;
    }

    /**
     * Group by $fEqual, items that equals will grouped together
     *
     * @phpstan-template T
     * @phpstan-param callable(T, T): bool $fEqual
     * @phpstan-param iterable<T> $values
     * @phpstan-return array<T[]>
     */
    public static function equalGroup(callable $fEqual, iterable $values): array
    {
        $groups = [];
        foreach ($values as $v) {
            $groupExist = false;
            foreach ($groups as $key => $g) {
                if ($fEqual($g[0], $v)) {
                    $groupExist = true;
                    $g[] = $v;
                    $groups[$key] = $g;
                    break;
                }
            }
            if (!$groupExist) {
                $groups[] = [$v];
            }
        }
        return $groups;
    }

    /**
     * Call $fCheck on item of $values, returns true if any callback result is true.
     * Returns false if $values is empty.
     *
     * @phpstan-template T
     * @phpstan-param callable(T): bool $fCheck
     * @phpstan-param iterable<T> $values
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
     *
     * @phpstan-template T
     * @phpstan-param callable(T): bool $fCheck
     * @phpstan-param iterable<T> $values
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
     *
     * @phpstan-param iterable<mixed> $values
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
     * @phpstan-template T
     * @phpstan-param T $v
     * @phpstan-return T
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
     *
     * @phpstan-template T
     * @phpstan-param T $v
     * @phpstan-param callable(): T $fInit
     * @phpstan-param (callable(T): bool)|null $fIsUninitialized accept the value to tell the value
     * is uninitialized, by default use `isEmpty().
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
     * If the property not set (isset() returns false), always init the property, ignores $fIsUninitialized.
     *
     * @phpstan-template K
     * @phpstan-template T
     *
     * @phpstan-param T[] $arr
     * @phpstan-param K $idx
     * @phpstan-param callable(): T $fInit
     * @phpstan-param callable(T): bool $fIsUninitialized test does the array item initialized.
     * @phpstan-return T[] the array item value.
     */
    public static function initArrayItem(array &$arr, $idx, callable $fInit, callable $fIsUninitialized = null)
    {
        if (!isset($arr[$idx]) || ($fIsUninitialized !== null && $fIsUninitialized($arr[$idx]))) {
            $arr[$idx] = $fInit();
        }
        return $arr[$idx] = $fInit();
    }

    /**
     * @phpstan-template K
     * @phpstan-template V
     * @phpstan-param callable(V): K $fKey, accept one argument: array item, returns key normally string.
     * @phpstan-param V[] $arr
     * @phpstan-return array<K, V>
     *
     * Returns associated array key is $fKey result, value is array value.
     */
    public static function toKeyed(callable $fKey, array $arr): array
    {
        $keys = array_map($fKey, $arr);
        /** @phpstan-var array<K, V>|false $r */
        $r = array_combine($keys, $arr);
        assert($r !== false);
        return $r;
    }

    /**
     * Test array or iterator, returns first item the $test callback returns true.
     * Returns $default value if no item matched.
     *
     * @phpstan-template T
     * @phpstan-param callable(T): bool $test
     * @phpstan-param iterable<T> $items
     * @phpstan-param T $default
     * @phpstan-return T
     */
    public static function first(callable $test, iterable $items, $default)
    {
        foreach ($items as $item) {
            if ($test($item)) {
                return $item;
            }
        }
        return $default;
    }

    /**
     * Test array or iterator, returns first item the $test callback returns true.
     * Returns null value if no item matched.
     *
     * @phpstan-template T
     * @phpstan-param callable(T): bool $test
     * @phpstan-param iterable<T> $items
     * @phpstan-return T|null
     */
    public static function firstOrNull(callable $test, iterable $items)
    {
        foreach ($items as $item) {
            if ($test($item)) {
                return $item;
            }
        }
        return null;
    }

    /**
     * @phpstan-template T
     * @phpstan-template K
     * @phpstan-param array<K, T> $arr
     * @phpstan-param K $key
     * @phpstan-param callable(K): T $fCreate, called if $key not exist in $arr,
     * accept one argument $key, and returns value.
     * @phpstan-return T
     */
    public static function getOrCreate(array &$arr, $key, callable $fCreate)
    {
        if (!key_exists($key, $arr)) {
            $arr[$key] = $fCreate($key);
        }
        return $arr[$key];
    }

    /**
     * Assert that the value is not null.
     *
     * @phpstan-template T
     * @phpstan-param T|null $v
     * @phpstanreturn T
     */
    public static function notNull($v, string $message = '')
    {
        if ($v === null) {
            throw new LogicException($message ?: "Expect value not null");
        }

        return $v;
    }
}
