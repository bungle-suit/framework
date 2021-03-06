<?php

declare(strict_types=1);

namespace Bungle\Framework\Model;

final class ArrayUtil
{
    /**
     * Insert item or items to array at the specific $key position.
     * @param mixed[] $array
     * @param string|int $key
     * @param mixed $item if it is an array, items are insert, or the single item will be insert.
     * To insert an array as item, wrap it with '[]'.
     */
    public static function insertAt(array &$array, $key, $item): void
    {
        if (is_int($key)) {
            array_splice($array, $key, 0, $item);
        } else {
            $pos = array_search($key, array_keys($array));
            assert($pos !== false);
            $part1 = array_slice($array, 0, $pos);
            $part2 = array_slice($array, $pos);
            assert($part1 !== false);
            assert($part2 !== false);
            $array = array_merge($part1, $item, $part2);
        }
    }

    /**
     * @template T
     * Remove $element from $array
     * @phpstan-param array<int|string, T> $array
     * @phpstan-param T $element
     * @param bool $reindex reindex after removing.
     * Return true if element found and removed.
     */
    public static function removeElement(array &$array, $element, bool $reindex = false): bool
    {
        $key = array_search($element, $array);
        if ($key === false) {
            return false;
        }

        unset($array[$key]);
        if ($reindex) {
            $array = array_values($array);
        }

        return true;
    }
}
