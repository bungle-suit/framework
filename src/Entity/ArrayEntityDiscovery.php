<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

use ArrayIterator;
use Iterator;

/**
 * Store Entity class in array, good for unit tests, and
 * simple case.
 */
class ArrayEntityDiscovery implements EntityDiscovererInterface
{
    /**
     * @var array<class-string<mixed>>
     */
    private array $entities;

    /**
     * @param array<class-string<mixed>> $entities
     */
    public function __construct(array $entities)
    {
        $this->entities = $entities;
    }

    public function getAllEntities(): Iterator
    {
        return new ArrayIterator($this->entities);
    }
}
