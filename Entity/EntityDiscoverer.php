<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

use Bungle\Framework\Annotation\High;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

class EntityDiscoverer implements EntityDiscovererInterface
{
    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function getAllEntities(): \Iterator
    {
        $documentManager = $this->managerRegistry->getManager();
        $list = $documentManager->getConfiguration()
                                          ->getMetadataDriverImpl()
                                          ->getAllClassNames();
        foreach ($list as $cls) {
            if (High::resolveHigh($cls)) {
                yield $cls;
            }
        }
    }
}
