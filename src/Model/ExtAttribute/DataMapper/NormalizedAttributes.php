<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute\DataMapper;

use ArrayAccess;
use Bungle\Framework\Model\ExtAttribute\AttributeDefinitionInterface;
use LogicException;

/**
 * Normalized form of attributes, used as attribute settings from normalized form.
 */
class NormalizedAttributes implements ArrayAccess
{
    private array $definitions;
    private array $dataSet;

    /**
     * @param array<string, AttributeDefinitionInterface> $definitions
     * @param array<string, mixed> $dataSet attribute values
     */
    public function __construct(array $definitions, array $dataSet)
    {
        $this->definitions = $definitions;
        $this->dataSet = $dataSet;
    }

    public function getDefinitions(): array
    {
        return $this->definitions;
    }

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
}
