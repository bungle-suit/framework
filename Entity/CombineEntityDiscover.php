<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

/**
 * Combine EntityDiscovererInterface into one.
 */
class CombineEntityDiscover implements EntityDiscovererInterface
{
    private array $discovers;

    public function __construct(array $discovers)
    {
        $this->discovers = $discovers;
    }

    public function getAllEntities(): \Iterator
    {
        foreach ($this->discovers as $item) {
            foreach ($item->getAllEntities() as $cls) {
                yield $cls;
            }
        }
    }
}
