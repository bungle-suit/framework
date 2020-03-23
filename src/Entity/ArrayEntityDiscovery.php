<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

/**
 * Store Entity class in array, good for unit tests, and
 * simple case.
 */
class ArrayEntityDiscovery implements EntityDiscovererInterface
{
    private array $entities;

    public function __construct(array $entities)
    {
        $this->entities = $entities;
    }

    public function getAllEntities(): \Iterator
    {
        return new \ArrayIterator($this->entities);
    }
}
