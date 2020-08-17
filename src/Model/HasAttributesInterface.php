<?php

declare(strict_types=1);

namespace Bungle\Framework\Model;

/**
 * Use Attributes trait to implement HasAttributesInterface.
 */
interface HasAttributesInterface
{
    /**
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name, $default = null);

    /**
     * @param mixed $val
     */
    public function set(string $name, $val): void;

    public function has(string $name): bool;

    public function remove(string $name): void;

    /**
     * @return mixed[]
     */
    public function all(): array;
}
