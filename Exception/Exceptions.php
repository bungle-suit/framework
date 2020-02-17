<?php

declare(strict_types=1);

namespace Bungle\Framework\Exception;

final class Exceptions
{
    private function __construct()
    {
    }

    public static function highNotDefinedOn(string $entityClass): \DomainException
    {
        return new \DomainException("No High prefix defined on entity '$entityClass'");
    }

    public static function highDuplicated(string $high, string $cls1, string $cls2): \DomainException
    {
        return new \DomainException("Entity class '$cls1' and '$cls2', has the same high code: $high");
    }

    public static function highNotFound(string $high): \DomainException
    {
        return new \DomainException("High not found: $high");
    }

    public static function entityNotDefined(string $entityClass): \DomainException
    {
        if (!class_exists($entityClass)) {
            return new \DomainException("No class '$entityClass', check your spell");
        }

        return new \DomainException("class '$entityClass' not declared as entity");
    }

    public function propertyNotFound(string $entityClass, string $propertyName): \DomainException
    {
        return new \DomainException("Can not found property $propertyName in entity $entityClass");
    }

    public function entityExpectDefaultConstructor(string $entityClass, $inner = null): \DomainException
    {
        return new \DomainException("Expect entity class $entityClass's __construct has zero argument", 0, $inner);
    }
}
