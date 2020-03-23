<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

// Interface to resolve entity meta by entity class name.
interface EntityMetaResolverInterface
{
    public function resolveEntityMeta(string $entityClass): EntityMeta;
}
