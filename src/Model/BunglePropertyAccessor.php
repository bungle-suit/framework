<?php

declare(strict_types=1);

namespace Bungle\Framework\Model;

use InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * Wrap exist PropertyAccessor plus one extra function:
 *
 * For getValue(), if $propertyPath is empty string, return the object itself.
 */
class BunglePropertyAccessor implements PropertyAccessorInterface
{
    private PropertyAccessorInterface $inner;

    public function __construct(PropertyAccessorInterface $inner)
    {
        $this->inner = $inner;
    }

    /**
     * @inheritDoc
     * @param object|mixed[]               $objectOrArray The object or array to modify
     * @param string|PropertyPathInterface $propertyPath  The property path to modify
     * @param mixed                        $value         The value to set at the end of the property path
     * @return void
     */
    public function setValue(&$objectOrArray, $propertyPath, mixed $value)
    {
        if ($propertyPath === '') {
            throw new InvalidArgumentException('BunglePropertyAccessor: setValue on self');
        }

        $this->inner->setValue($objectOrArray, $propertyPath, $value);
    }

    /**
     * @inheritDoc
     * @param object|array                 $objectOrArray The object or array to modify
     * @param string|PropertyPathInterface $propertyPath  The property path to modify
     * @return mixed
     */
    public function getValue($objectOrArray, $propertyPath)
    {
        if ($propertyPath === '') {
            return $objectOrArray;
        }

        return $this->inner->getValue($objectOrArray, $propertyPath);
    }

    /**
     * @inheritDoc
     * @param object|array                 $objectOrArray The object or array to modify
     * @param string|PropertyPathInterface $propertyPath  The property path to modify
     * @return bool
     */
    public function isWritable($objectOrArray, $propertyPath)
    {
        if ($propertyPath === '') {
            return false;
        }

        return $this->inner->isWritable($objectOrArray, $propertyPath);
    }

    /**
     * @inheritDoc
     * @param object|array                 $objectOrArray The object or array to modify
     * @param string|PropertyPathInterface $propertyPath  The property path to modify
     * @return bool
     */
    public function isReadable($objectOrArray, $propertyPath)
    {
        if ($propertyPath === '') {
            return true;
        }

        return $this->inner->isReadable($objectOrArray, $propertyPath);
    }
}
