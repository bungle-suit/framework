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
     * @param array<string, string|callable(T): mixed> $props
     * @return callable(T): array<string, mixed>
     */
    public function assocArrayFrom(array $props): callable
    {
        return function ($o) use ($props): array {
            $ret = [];
            foreach ($props as $k => $v) {
                $ret[$k] = is_callable($v) ? $v($o) : $this->propAcc->getValue($o, $v);
            }

            return $ret;
        };
    }
}
