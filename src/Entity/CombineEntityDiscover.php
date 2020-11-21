<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

use Iterator;

/**
 * Combine EntityDiscovererInterface into one.
 */
class CombineEntityDiscover implements EntityDiscovererInterface
{
    /** @var EntityDiscovererInterface[] */
    private array $discovers;

    /**
     * @param EntityDiscovererInterface[] $discovers
     */
    public function __construct(array $discovers)
    {
        $this->discovers = $discovers;
    }

    public function getAllEntities(): Iterator
    {
        foreach ($this->discovers as $item) {
            foreach ($item->getAllEntities() as $cls) {
                yield $cls;
            }
        }
    }
}
