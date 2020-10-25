<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

class AttributeSetDefinition
{
    private array $attributeDefinitions;
    /**
     * @phpstan-var callable(string $attrName): AttributeInterface
     */
    private $fCreateAttribute;

    /**
     * @param array<string, AttributeDefinitionInterface> $attributeDefinitions
     * @param callable(string $attrName): AttributeInterface create a new attribute object.
     */
    public function __construct(array $attributeDefinitions, callable $fCreateAttribute)
    {
        $this->attributeDefinitions = $attributeDefinitions;
        $this->fCreateAttribute = $fCreateAttribute;
    }

    public function getAttributeDefinitions(): array
    {
        return $this->attributeDefinitions;
    }

    /**
     * Create an empty attribute.
     */
    public function create(string $attrName): AttributeInterface
    {
        return ($this->fCreateAttribute)($attrName);
    }
}
