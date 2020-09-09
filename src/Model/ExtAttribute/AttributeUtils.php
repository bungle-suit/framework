<?php
declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

class AttributeUtils
{
    /**
     * Get bool attribute value from $attributes.
     * @param AttributeInterface[] $attributes
     */
    public static function getBoolAttribute(array $attributes, string $attrName): bool
    {
        foreach ($attributes as $attribute) {
            if ($attribute->getAttribute() === $attrName) {
                return $attribute->asBool();
            }
        }
        return false;
    }
}
