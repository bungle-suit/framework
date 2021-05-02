<?php

declare(strict_types=1);

namespace Bungle\Framework;

use InvalidArgumentException;
use Iterator;
use LogicException;
use Traversable;
use Webmozart\Assert\Assert;

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
        return fn(object $o) => $o->$name;
    }

    /**
     * @param string $method getter method name, must use full name, such as 'getName', 'hasRole'.
     * @return callable call specific getter function from an object.
     */
    public static function getter(string $method): callable
    {
        return fn(object $o) => $o->$method();
    }

    /**
     * @return callable(): null do nothing but returns null.
     */
    public static function null(): callable
    {
        return fn() => null;
    }

    /**
     * @return callable(): true that always returns true.
     */
    public static function t(): callable
    {
        return fn() => true;
    }

    /**
     * @return callable(): false that always returns false.
     */
    public static function f(): callable
    {
        return fn() => false;
    }

    /**
     * Group array/iterator, key returned by $fKey.
     *
     * @template T
     * @template K
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
     * @template T
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
     * @template T
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
     * @template T
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
     * @template T
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
     * @template T
     * @phpstan-param T $v
     * @phpstan-param callable(): T $fInit
     * @phpstan-param (callable(T): bool)|null $fIsUninitialized accept the value to tell the value
     * is uninitialized, by default use `isEmpty().
     *
     * @return mixed returns $v
     * @phpstan-return T
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
     * If the property not set (isset() returns false), always init the property, ignores
     *     $fIsUninitialized.
     *
     * @return mixed the property value
     */
    public static function initProperty(
        object $o,
        string $property,
        callable $fInit,
        callable $fIsUninitialized = null
    ) {
        if (!isset($o->$property) ||
            ($fIsUninitialized !== null && $fIsUninitialized($o->$property))) {
            $o->$property = $fInit();
        }

        return $o->$property;
    }

    /**
     * If array item at specific $idx not initialized, call $fInit to init the array item.
     *
     * If the property not set (isset() returns false), always init the property, ignores
     * $fIsUninitialized.
     *
     * @template K
     * @template T
     *
     * @phpstan-param T[] $arr
     * @phpstan-param K $idx
     * @phpstan-param callable(): T $fInit
     * @phpstan-param callable(T): bool $fIsUninitialized test does the array item initialized.
     * @phpstan-return T[] the array item value.
     */
    public static function initArrayItem(
        array &$arr,
        $idx,
        callable $fInit,
        callable $fIsUninitialized = null
    ) {
        if (!isset($arr[$idx]) || ($fIsUninitialized !== null && $fIsUninitialized($arr[$idx]))) {
            $arr[$idx] = $fInit();
        }

        return $arr[$idx] = $fInit();
    }

    /**
     * @template K
     * @template V
     * @phpstan-param callable(V): K $fKey , accept one argument: array item, returns key normally
     *     string.
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
     * @template T
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
     * @template T
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
     * @template T
     * @template K
     * @phpstan-param array<K, T> $arr
     * @phpstan-param K $key
     * @phpstan-param callable(K): T $fCreate , called if $key not exist in $arr,
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
     */
    public static function notNull(mixed $v, string $message = ''): mixed
    {
        if ($v === null) {
            throw new LogicException($message ?: "Expect value not null");
        }

        return $v;
    }

    /**
     * @param callable(mixed...): bool $a
     * @param array<callable(mixed...): bool> $b
     * @return callable(mixed...): bool bool
     */
    public static function and(callable $a, ...$b): callable
    {
        return function (...$args) use ($b, $a) {
            if (!$a(...$args)) {
                return false;
            }
            /** @var callable(mixed...):bool $f */
            foreach ($b as $f) {
                if (!$f(...$args)) {
                    return false;
                }
            }

            return true;
        };
    }

    /**
     * @param callable(mixed...): bool $a
     * @param array<callable(mixed...): bool> $b
     * @return callable(mixed...): bool
     */
    public static function or(callable $a, ...$b): callable
    {
        return function (...$args) use ($b, $a) {
            if ($a(...$args)) {
                return true;
            }
            /** @var callable(mixed...):bool $f */
            foreach ($b as $f) {
                if ($f(...$args)) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * @param callable(mixed...): bool $a
     * @return callable(mixed...): bool
     */
    public static function not(callable $a): callable
    {
        return function (...$args) use ($a) {
            return !$a(...$args);
        };
    }

    /**
     * @template T
     * @param callable(mixed...): bool $cond call $a if returns true, $b otherwise.
     * @param callable(mixed...): T $a
     * @param callable(mixed...): T $b
     * @return callable(mixed...): T
     */
    public static function if(callable $cond, callable $a, callable $b): callable
    {
        return function (...$args) use ($cond, $a, $b) {
            if ($cond(...$args)) {
                return $a(...$args);
            }

            return $b(...$args);
        };
    }

    /**
     * @template T
     * @template V
     * @param callable(mixed...): V $fValue prepare value for case function/values.
     * @param array<(array{V|callable(V, mixed...): bool, callable(mixed...):
     *     T})|(callable(mixed...): T)> $cases Like @see self::if(), but a select/case expression,
     *     must provide function to handle default case, because it is an expression, must has
     *     return value. If case value, provided use strict equal (===).
     */
    public static function select(callable $fValue, ...$cases): callable
    {
        return function (...$args) use ($fValue, $cases) {
            if (count($cases) % 2 === 0) {
                throw new InvalidArgumentException('select must provide default case');
            }

            $v = $fValue(...$args);
            for ($i = 0, $l = count($cases); ($i + 1) < $l; $i += 2) {
                $fSelect = $cases[$i];
                if (is_callable($fSelect) ? $fSelect(...$args) : $v === $fSelect) {
                    $action = $cases[$i + 1];
                    Assert::isCallable($action);

                    return $action(...$args);
                }
            }

            $defCase = end($cases);
            Assert::isCallable($defCase);

            return $defCase(...$args);
        };
    }

    /**
     * @template T
     * @phpstan-param (\ArrayAccess<mixed, T>&\Countable)|array<T> $arr
     * @phpstan-return T
     * @throws LogicException if no last element
     */
    public static function last($arr)
    {
        $lastIdx = count($arr) - 1;
        if ($lastIdx < 0) {
            throw new LogicException('No last element, collection is empty');
        }

        return $arr[$lastIdx];
    }

    /**
     * @template T
     * @param callable(T): bool $f
     * @phpstan-param iterable<T> $iterable
     * @phpstan-return Traversable<T>
     */
    public static function filter(callable $f, iterable $iterable): Traversable
    {
        foreach ($iterable as $item) {
            if ($f($item)) {
                yield $item;
            }
        }
    }

    /**
     * @template U
     * @template V
     * @param callable(U): V $f
     * @phpstan-param iterable<U> $iterable
     * @phpstan-return Traversable<V>
     */
    public static function map(callable $f, iterable $iterable): Traversable
    {
        foreach ($iterable as $item) {
            yield $f($item);
        }
    }

    /**
     * Alter array keys
     * @template V
     * @param callable(string): string $f
     * @param array<V> $iterable
     * @return array<V> Returns new array with keys changed.
     */
    public static function mapKeys(callable $f, array $arr): array
    {
        $r = [];
        foreach ($arr as $key => $val) {
            $r[$f($key)] = $val;
        }

        return $r;
    }

    /**
     * Return count/length of $iterable.
     *
     * @template T
     * @phpstan-param iterable<T> $iterable
     */
    public static function count(iterable $iterable): int
    {
        $r = 0;
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($iterable as $_) {
            $r++;
        }

        return $r;
    }

    /**
     * Call $inner only once, cache result value, and return
     * on later calls.
     *
     * Currently, only no-arg $inner supported.
     */
    public static function once(callable $inner): callable
    {
        return function () use ($inner, &$cached) {
            if (isset($cached)) {
                return $cached;
            }

            return $cached = $inner();
        };
    }

    /**
     * @template T
     * Return the only item from array.
     * @param T[] $arr
     * @return T
     * @throws LogicException if array is empty, or has more items
     */
    public static function onlyItem(array $arr): mixed
    {
        if (self::count($arr) !== 1) {
            throw new LogicException('Requires array has only 1 item, but got '.count($arr));
        }

        reset($arr);

        return current($arr);
    }
}
