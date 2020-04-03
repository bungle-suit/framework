<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

/**
 * Service to get EntityMeta by high or class.
 * @deprecated use EntityRegistry
 */
class EntityMetaRepository
{
    private array $metaByClass = [];
    private EntityMetaResolverInterface $resolver;

    public function __construct(EntityMetaResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    // Get entity meta by high.
    public function get(string $class): EntityMeta
    {
        if (isset($this->metaByClass[$class])) {
            return $this->metaByClass[$class];
        }

        $meta = $this->resolver->resolveEntityMeta($class);
        $this->metaByClass[$class] = $meta;

        return $meta;
    }
}
