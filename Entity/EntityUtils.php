<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

use Bungle\Framework\Exception\Exceptions;

/**
 * Helper to operate on Entity class.
 */
final class EntityUtils
{
    /**
     * Create an instance of specific entity class.
     *
     * Entity class must have empty constructor.  */
    public function create(string $entityClass): object
    {
        try {
            return new $entityClass();
        } catch (\ArgumentCountError $e) {
            throw Exceptions::entityExpectDefaultConstructor($entityClass, $e);
        }
    }
}
