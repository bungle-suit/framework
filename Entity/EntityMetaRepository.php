<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

// TODO: EntityDiscovererInterface driven by high, change it to by class,
// and EntityMetaRepository::get() driven by class either.
// Most of symfony code relies on class, use high is inconvenience.

/**
 * Service to get EntityMeta by high or class.
 */
class EntityMetaRepository
{
    private array $metaByHigh = [];
    private EntityMetaResolverInterface $resolver;
    private EntityRegistry $entityRegistry;

    public function __construct(EntityRegistry $entityRegistry, EntityMetaResolverInterface $resolver)
    {
        $this->entityRegistry = $entityRegistry;
        $this->resolver = $resolver;
    }

    // Get entity meta by high.
    public function get(string $high): EntityMeta
    {
        if (isset($this->metaByHigh[$high])) {
            return $this->metaByHigh[$high];
        }

        $cls = $this->entityRegistry->getEntityByHigh($high);
        $meta = $this->resolver->resolveEntityMeta($cls);
        $this->metaByHigh[$high] = $meta;

        return $meta;
    }
}
