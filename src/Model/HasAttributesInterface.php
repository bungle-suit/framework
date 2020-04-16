<?php

declare(strict_types=1);

namespace Bungle\Framework\Model;

/**
 * Use Attributes trait to implement HasAttributesInterface.
 */
interface HasAttributesInterface
{
    public function get(string $name, $default = null);

    public function set(string $name, $val): void;

    public function has(string $name): bool;

    public function remove(string $name): void;

    public function all(): array;
}
