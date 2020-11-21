<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

use ArgumentCountError;
use Bungle\Framework\Exceptions;

/**
 * Helper to operate on Entity class.
 */
final class EntityUtils
{
    /**
     * Create an instance of specific entity class.
     *
     * Entity class must have empty constructor.
     * @template T
     * @phpstan-param class-string<T> $entityClass
     * @phpstan-return T
     */
    public static function create(string $entityClass)
    {
        try {
            return new $entityClass();
        } catch (ArgumentCountError $e) {
            throw Exceptions::entityExpectDefaultConstructor($entityClass, $e);
        }
    }
}
