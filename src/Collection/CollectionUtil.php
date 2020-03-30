<?php
declare(strict_types=1);

namespace Bungle\Framework\Collection;

/**
 * Collection related util functions.
 */
final class CollectionUtil
{
    /**
     * @param callable $fKey, accept one argument: array item, returns key normally string.
     *
     * Returns associated array key is $fKey result, value is array value.
     */
    public static function toKeyed(callable $fKey, array $arr): array
    {
        $keys = array_map($fKey, $arr);
        return array_combine($keys, $arr);
    }

    /**
     * @param int|string $key
     * @param callable $fCreate , called if $key not exist in $arr, accept one argument $key, and returns value.
     * @return mixed
     */
    public static function getOrCreate(array &$arr, $key, callable $fCreate)
    {
        if (!key_exists($key, $arr)) {
            $arr[$key] = $fCreate($key);
        }
        return $arr[$key];
    }
}
