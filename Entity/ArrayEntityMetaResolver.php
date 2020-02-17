<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

final class ArrayEntityMetaResolver implements EntityMetaResolverInterface
{
    private array $metaByClass;

    public function __construct(array $metas)
    {
        $this->metaByClass = $metas;
    }

    public function resolveEntityMeta(string $entityClass): EntityMeta
    {
        return $this->metaByClass[$entityClass];
    }
}
