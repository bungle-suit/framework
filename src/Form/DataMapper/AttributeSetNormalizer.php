<?php

declare(strict_types=1);

namespace Bungle\Framework\Form\DataMapper;

use Bungle\Framework\FP;
use Bungle\Framework\Model\ExtAttribute\AttributeDefinitionInterface;
use Bungle\Framework\Model\ExtAttribute\AttributeInterface;
use LogicException;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Convert attribute array to [attrName -> attrValue] array.
 */
class AttributeSetNormalizer implements DataTransformerInterface
{
    /**
     * @var array<string, AttributeDefinitionInterface>
     */
    private array $definitions;

    /**
     * @var callable(string $attrName): AttributeInterface
     */
    private $createAttribute;

    /**
     * @param array<int, AttributeDefinitionInterface> $definitions
     * @param callable(string $attrName): AttributeInterface $createAttribute create a new attribute object.
     */
    public function __construct(array $definitions, callable $createAttribute)
    {
        $this->definitions = FP::toKeyed(
            fn(AttributeDefinitionInterface $attr) => $attr->getName(),
            $definitions
        );
        $this->createAttribute = $createAttribute;
    }

    /**
     * @param AttributeInterface[] $value
     * @return array<string, mixed>
     */
    public function transform($value)
    {
        $r = [];
        foreach ($this->definitions as $attr) {
            $r[$attr->getName()] = $attr->createDefault();
        }

        foreach ($value as $attr) {
            $def = $this->getDefinition($attr->getAttribute());
            $r[$def->getName()] = $def->restoreValue($attr);
        }

        return $r;
    }

    /**
     * @param array<string, mixed> $value
     * @return AttributeInterface[]
     */
    public function reverseTransform($value)
    {
        assert(is_array($value));

        $r = [];
        foreach ($value as $name => $val) {
            $attr = ($this->createAttribute)($name);
            $def = $this->getDefinition($name);
            $def->saveValue($attr, $val);
            if ($attr->getValue() !== '') {
                $r[] = $attr;
            }
        }
        return $r;
    }

    private function getDefinition(string $name): AttributeDefinitionInterface
    {
        $r = $this->definitions[$name] ?? null;
        if ($r === null) {
            throw new LogicException("Unknown attribute $name");
        }
        return $r;
    }
}
