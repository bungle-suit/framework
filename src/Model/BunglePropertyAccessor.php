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
     */
    public function setValue(object|array &$objectOrArray, string|PropertyPathInterface $propertyPath, mixed $value)
    {
        if ($propertyPath === '') {
            throw new InvalidArgumentException('BunglePropertyAccessor: setValue on self');
        }

        $this->inner->setValue($objectOrArray, $propertyPath, $value);
    }

    /**
     * @inheritDoc
     */
    public function getValue(object|array $objectOrArray, string|PropertyPathInterface $propertyPath): mixed
    {
        if ($propertyPath === '') {
            return $objectOrArray;
        }

        return $this->inner->getValue($objectOrArray, $propertyPath);
    }

    /**
     * @inheritDoc
     */
    public function isWritable(object|array $objectOrArray, string|PropertyPathInterface $propertyPath): bool
    {
        if ($propertyPath === '') {
            return false;
        }

        return $this->inner->isWritable($objectOrArray, $propertyPath);
    }

    /**
     * @inheritDoc
     */
    public function isReadable(object|array $objectOrArray, string|PropertyPathInterface $propertyPath): bool
    {
        if ($propertyPath === '') {
            return true;
        }

        return $this->inner->isReadable($objectOrArray, $propertyPath);
    }
}
