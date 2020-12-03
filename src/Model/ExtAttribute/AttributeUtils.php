<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

use Bungle\Framework\FP;

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
     * @return 0 if attribute not exist.
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

    /**
     * @template T
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
