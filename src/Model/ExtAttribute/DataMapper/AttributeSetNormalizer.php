<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute\DataMapper;

use Bungle\Framework\FP;
use Bungle\Framework\Model\ExtAttribute\AttributeDefinitionInterface;
use Bungle\Framework\Model\ExtAttribute\AttributeInterface;
use Bungle\Framework\Model\ExtAttribute\AttributeSetDefinition;
use LogicException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @template T of AbstractNormalizedAttributes
 * Convert attribute array to @see AbstractNormalizedAttributes.
 */
class AttributeSetNormalizer implements DataTransformerInterface
{
    private array $definitions;
    private AttributeSetDefinition $definition;
    /** @phpstan-var class-string<T> */
    private string $normalizedAttributeSetClass;

    /**
     * @phpstan-param class-string<T> $normalizedAttributeSetClass
     */
    public function __construct(string $normalizedAttributeSetClass)
    {
        $this->definition = ([$normalizedAttributeSetClass, 'getDefinition'])();
        $this->definitions = FP::toKeyed(
            fn(AttributeDefinitionInterface $attr) => $attr->getName(),
            $this->definition->getAttributeDefinitions()
        );
        $this->normalizedAttributeSetClass = $normalizedAttributeSetClass;
    }

    /**
     * @param AttributeInterface[] $value
     * @phpstan-return T
     * @return AbstractNormalizedAttributes
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

        $cls = $this->normalizedAttributeSetClass;
        return new $cls($r);
    }

    /**
     * @param AbstractNormalizedAttributes $value
     *
     * @phpstan-param T $value
     * @return AttributeInterface[]
     */
    public function reverseTransform($value)
    {
        assert(get_class($value) === $this->normalizedAttributeSetClass);

        $r = [];
        foreach ($value->toArray() as $name => $val) {
            $attr = $this->definition->create($name);
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
