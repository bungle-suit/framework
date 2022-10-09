<?php

declare(strict_types=1);

namespace Bungle\Framework;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class Converters
{
    public function __construct(private PropertyAccessorInterface $propAcc)
    {
    }

    /**
     * Create assoc array from string or callback, use PropertyAccessor to
     * get value from $o.
     * @template T
     * @param array<string, string|callable|array{string, callable}> $props ,
     *  can be prop path, callable($o) or [prop path, callable($propValue, $o)],
     * in third case, callable convert value get from path.
     * @return callable(T): array<string, mixed>
     */
    public function assocArrayFrom(array $props): callable
    {
        return function ($o) use ($props): array {
            $ret = [];
            foreach ($props as $k => $v) {
                $ret[$k] = $this->convertValue($v, $o);
            }

            return $ret;
        };
    }

    private function convertValue($pathOrF, $o): mixed
    {
        if (is_callable($pathOrF)) {
            return $pathOrF($o);
        } elseif (is_array($pathOrF)) {
            return $pathOrF[1]($this->propAcc->getValue($o, $pathOrF[0]), $o);
        } else {
            return $this->propAcc->getValue($o, $pathOrF);
        }
    }

    /**
     * Create list array from string or callback, use PropertyAccessor to
     * get value from $o.
     * @template T
     * @param (string|callable(T): mixed)[] $props
     * @return callable(T): array
     */
    public function listArrayFrom(array $props): callable
    {
        return function ($o) use ($props): array {
            $ret = [];
            foreach ($props as $v) {
                $ret[] = $this->convertValue($v, $o);
            }

            return $ret;
        };
    }
}
