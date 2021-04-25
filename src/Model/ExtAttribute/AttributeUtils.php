<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

use Bungle\Framework\FP;
use Symfony\Component\Form\FormBuilderInterface;

class AttributeUtils
{
    /**
     * Get bool attribute value from $attributes.
     * @param AttributeInterface[] $attributes
     */
    public static function getBoolAttribute(array $attributes, string $attrName): bool
    {
        return self::getAttributeValue(
            $attributes,
            $attrName,
            false,
            fn(AttributeInterface $attr) => $attr->asBool()
        );
    }

    /**
     * Get float attribute value.
     * @param AttributeInterface[] $attributes
     * return 0 if attribute not exist.
     */
    public static function getFloatAttribute(array $attributes, string $attrName): float
    {
        return self::getAttributeValue(
            $attributes,
            $attrName,
            0.0,
            fn(AttributeInterface $attr) => $attr->asFloat()
        );
    }

    public static function addForm(
        FormBuilderInterface $formBuilder,
        AttributeDefinitionInterface $def,
    ): void {
        $options = [
            'label' => $def->getLabel(),
            'required' => false,
        ];
        if ($def->getDescription()) {
            $options['help'] = $def->getDescription();
        }
        $options = array_merge($options, $def->getFormOption());
        $formBuilder->add($def->getName(), $def->getFormType(), $options);
    }

    /**
     * @template T
     * @param AttributeInterface[] $attributes
     * @phpstan-param T $defValue
     * @phpstan-param callable(AttributeInterface): T $getValue
     * @phpstan-return T
     */
    private static function getAttributeValue(
        array $attributes,
        string $attrName,
        $defValue,
        callable $getValue
    ) {
        $attr = FP::firstOrNull(
            fn(AttributeInterface $attr) => $attr->getAttribute() === $attrName,
            $attributes
        );

        if ($attr === null) {
            return $defValue;
        }

        return $getValue($attr);
    }
}
