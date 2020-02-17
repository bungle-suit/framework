<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

use Bungle\Framework\Exception\Exceptions;

class EntityRegistry
{
    // array of entities full class name.
    public array $entities;
    private HighResolverInterface $highResolver;
    private array $highClsMap;

    public function __construct(EntityDiscovererInterface $discoverer, HighResolverInterface $highResolver)
    {
        $this->highResolver = $highResolver;
        $this->entities = iterator_to_array($discoverer->getAllEntities(), false);
    }

    /**
     * Get high prefix by clsName.
     */
    public function getHigh(string $clsName): string
    {
        if (!isset($this->highClsMap)) {
            $this->highClsMap = $this->scanMap($this->entities);
        }

        if (!($r = array_search($clsName, $this->highClsMap))) {
            if (!\in_array($clsName, $this->entities)) {
                throw Exceptions::entityNotDefined($clsName);
            }
        }

        return $r;
    }

    /**
     * Get Entity class by high prefix.
     */
    public function getEntityByHigh(string $high): string
    {
        if (!isset($this->highClsMap)) {
            $this->highClsMap = $this->scanMap($this->entities);
        }

        if (!($r = $this->highClsMap[$high] ?? '')) {
            throw Exceptions::highNotFound($high);
        }

        return $r;
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
}
