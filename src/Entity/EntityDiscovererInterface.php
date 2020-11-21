<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

use Iterator;

/**
 * Interface support to discover all entity class full names.
 */
interface EntityDiscovererInterface
{
    /**
     * @return Iterator<class-string<mixed>>
     */
    public function getAllEntities(): Iterator;
}
