<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

use Bungle\Framework\FP;

class AttributeSetDefinition
{
    private array $attributeDefinitions;
    /**
     * @phpstan-var callable(string $attrName): AttributeInterface
     */
    private $fCreateAttribute;

    /**
     * @param array<int, AttributeDefinitionInterface> $attributeDefinitions
     * @param callable(string $attrName): AttributeInterface create a new attribute object.
     */
    public function __construct(array $attributeDefinitions, callable $fCreateAttribute)
    {
        $this->attributeDefinitions = FP::toKeyed(fn ($d) => $d->getName(), $attributeDefinitions);
        $this->fCreateAttribute = $fCreateAttribute;
    }

    /**
     * @return array<string, AttributeDefinitionInterface>
     */
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
