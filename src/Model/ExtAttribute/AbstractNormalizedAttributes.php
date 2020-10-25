<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

use ArrayAccess;
use LogicException;

/**
 * Normalized form of attributes, used as attribute settings from normalized form.
 *
 * Derive from this to create attribute set.
 */
abstract class AbstractNormalizedAttributes implements ArrayAccess
{
    private array $dataSet;

    /**
     * @param array<string, AttributeDefinitionInterface> $definitions
     * @param array<string, mixed> $dataSet attribute values
     */
    public function __construct(array $dataSet)
    {
        $this->dataSet = $dataSet;
    }

    private static AttributeSetDefinition $definitions;

    public static function getDefinition(): AttributeSetDefinition
    {
        return self::$definitions ?? (self::$definitions = static::createDefinition());
    }

    abstract protected static function createDefinition(): AttributeSetDefinition;

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->dataSet);
    }

    public function offsetGet($offset)
    {
        assert(array_key_exists($offset, $this->dataSet), "Attribute $offset not exist");

        return $this->dataSet[$offset];
    }

    public function offsetSet($offset, $value)
    {
        assert(array_key_exists($offset, $this->dataSet), "Attribute $offset not exist");

        $this->dataSet[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        throw new LogicException('Not supported');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->dataSet;
    }
}
