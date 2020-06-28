<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

use Bungle\Framework\Exceptions;
use Bungle\Framework\FP;
use function in_array;

class EntityRegistry
{
    // array of entities full class name.
    /** @var string[] $entities */
    private array $entities;
    private HighResolverInterface $highResolver;
    /** @var string[] */
    private array $highClsMap;

    /** @var array EntityMeta[] */
    private array $metaByClass = [];
    private EntityMetaResolverInterface $metaResolver;
    private EntityDiscovererInterface $discoverer;

    public function __construct(
        EntityDiscovererInterface $discoverer,
        HighResolverInterface $highResolver,
        EntityMetaResolverInterface $metaResolver
    ) {
        $this->highResolver = $highResolver;
        $this->metaResolver = $metaResolver;
        $this->discoverer = $discoverer;
    }

    /**
     * Like getHigh(), return empty if not High defined on
     * $clsName instead of throw exception.
     */
    public function getHighSafe(string $clsName): string
    {
        if (!isset($this->highClsMap)) {
            $this->highClsMap = $this->scanMap($this->getEntities());
        }

        if (!($r = array_search($clsName, $this->highClsMap))) {
            if (!in_array($clsName, $this->getEntities())) {
                return '';
            }
        }

        return $r;
    }

    /**
     * Get high prefix by clsName.
     */
    public function getHigh(string $clsName): string
    {
        $r = $this->getHighSafe($clsName);

        if (!$r) {
            throw Exceptions::entityNotDefined($clsName);
        }

        return $r;
    }

    /**
     * Get Entity class by the high prefix.
     */
    public function getEntityByHigh(string $high): string
    {
        if (!isset($this->highClsMap)) {
            $this->highClsMap = $this->scanMap($this->getEntities());
        }

        if (!($r = $this->highClsMap[$high] ?? '')) {
            throw Exceptions::highNotFound($high);
        }

        return $r;
    }

    /**
     * Shortcut method to create entity object by high,
     * Entity must have public zero-less constructor.
     */
    public function createEntity(string $high)
    {
        $cls = $this->getEntityByHigh($high);

        return EntityUtils::create($cls);
    }

    // Get entity meta
    public function getEntityMeta(string $class): EntityMeta
    {
        return FP::getOrCreate(
            $this->metaByClass,
            $class,
            fn (string $class) => $this->metaResolver->resolveEntityMeta($class)
        );
    }

    private function scanMap(array $entities): array
    {
        $r = [];
        foreach ($entities as $cls) {
            $high = $this->highResolver->resolveHigh($cls);
            if (!$high) {
                throw Exceptions::highNotDefinedOn($cls);
            }

            if (array_key_exists($high, $r)) {
                throw Exceptions::highDuplicated($high, $r[$high], $cls);
            }
            $r[$high] = $cls;
        }

        return $r;
    }

    public function getEntities(): array
    {
        if (!isset($this->entities)) {
            $this->entities = iterator_to_array($this->discoverer->getAllEntities(), false);
        }
        return $this->entities;
    }
}
