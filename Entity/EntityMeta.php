<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

use Bungle\Framework\Exception\Exceptions;

final class EntityMeta
{
    // key is property name, value is EntityPropertyMeta
    public array $properties;

    // Entity class name with namespace.
    public string $fullName;

    public string $logicName;

    public function __construct(string $fullName, string $logicName, array $properties)
    {
        $this->fullName = $fullName;
        $this->logicName = $logicName;
        $this->properties = array_combine(
            array_map(fn (EntityPropertyMeta $p) => $p->name, $properties),
            $properties,
        );
    }

    // Get property meta by name, raise error if no such property.
    public function getProperty(string $name): EntityPropertyMeta
    {
        if (!isset($this->properties[$name])) {
            throw Exceptions::propertyNotFound($this->fullName, $name);
        }

        return $this->properties[$name];
    }

    // Entity class name without namespace.
    public function name(): string
    {
        $words = explode('\\', $this->fullName);

        return $words[array_key_last($words)];
    }
}
