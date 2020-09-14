<?php

declare(strict_types=1);

namespace Bungle\Framework\Model;

/**
 * Attributes mixin add get/set attribute methods.
 *
 * Useful for context classes.
 */
trait HasAttributes
{
    /**
     * @var mixed[]
     */
    private array $attributes = [];

    /**
     * Init attributes with $values
     * @param array<string, mixed> $values
     */
    private function initAttributes(array $values): void
    {
        $this->attributes = $values;
    }

    /**
     * Returns attribute value, returns default if not exist.
     *
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        return $this->has($name) ? $this->attributes[$name] : $default;
    }

    /**
     * Set attribute.
     */
    public function set(string $name, $val): void
    {
        $this->attributes[$name] = $val;
    }

    /**
     * Test if has that the attribute.
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * Remove attribute, ignore if attribute not exist.
     */
    public function remove(string $name): void
    {
        unset($this->attributes[$name]);
    }

    /**
     * Returns all attributes.
     * @return mixed[]
     */
    public function all(): array
    {
        return $this->attributes;
    }
}
