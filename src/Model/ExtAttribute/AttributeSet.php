<?php
declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

use Bungle\Framework\FP;
use LogicException;
use Symfony\Component\Form\FormBuilderInterface;

class AttributeSet
{
    /** @var array<string, AttributeDefinitionInterface> */
    private array $definitions;

    /**
     * @param AttributeDefinitionInterface[] $definitions
     */
    public function __construct(array $definitions)
    {
        $this->definitions = FP::toKeyed(
            fn(AttributeDefinitionInterface $attr) => $attr->getName(),
            $definitions
        );
    }

    /**
     * @return array<string, AttributeDefinitionInterface> name keyed array
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * @return array<string, mixed> name -> default value dataset.
     */
    public function initDataSet(): array
    {
        $r = [];
        foreach ($this->definitions as $attr) {
            $r[$attr->getName()] = $attr->createDefault();
        }
        return $r;
    }

    /**
     * Init dataset, and restore data form $attributes.
     * @param AttributeInterface]] $attributes
     * @return array<string, mixed> name -> value.
     */
    public function fromAttributes(array $attributes): array
    {
        $r = $this->initDataSet();

        /** @var AttributeInterface $attr */
        foreach ($attributes as $attr) {
            $def = $this->getDefinition($attr->getAttribute());
            $r[$def->getName()] = $def->restoreValue($attr);
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

    /**
     * Save current dataset to attributes.
     * @phpstan-param callable(string): AttributeInterface Take attribute name returns new Attribute object.
     * @param AttributeInterface]] $attributes from external storage, such as database.
     * @param array<string, mixed> $dataset
     *
     * If updated attribute value not default,
     *   1. exist in $attributes, update the attribute value
     *   2. not exist in $attributes, create new attribute object.
     * If update attribute value is default,
     *   1. exist in $attributes, set the value to default, caller should delete it.
     *   2. not exist in $attributes, add no attribute object in result set.
     */
    public function toAttributes(callable $attrCreator, array $attributes, array $dataset): array
    {
        $attributes = FP::toKeyed(fn (AttributeInterface $attr) => $attr->getAttribute(), $attributes);
        $r = [];
        foreach ($dataset as $name => $value) {
            /** @var AttributeInterface $attr */
            $attr = $attributes[$name] ?? null;
            if ($isNew = ($attr === null)) {
                $attr = $attrCreator($name);
            }
            $def = $this->getDefinition($name);
            $def->saveValue($attr, $value);
            if (!$isNew || $attr->getValue() !== '') {
                $r[] = $attr;
            }
        }
        return $r;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var AttributeDefinitionInterface $definition */
        foreach ($this->definitions as $definition) {
            $definition->buildForm($builder, $options);
        }
    }
}
