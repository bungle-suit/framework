<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

use Bungle\Framework\Annotation\High;
use Doctrine\Persistence\ManagerRegistry;
use Iterator;

class ORMEntityDiscoverer implements EntityDiscovererInterface
{
    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function getAllEntities(): Iterator
    {
        $manager = $this->managerRegistry->getManager();
        $list = $manager->getMetadataFactory()->getAllMetadata();
        foreach ($list as $clsMeta) {
            /** @var class-string<mixed> $cls */
            $cls = $clsMeta->getName();
            if (High::resolveHigh($cls)) {
                yield $cls;
            }
        }
    }
}
