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
     * Like getHigh(), return empty if not High defined on
     * $clsName instead of throw exception.
     */
    public function getHighSafe(string $clsName): string
    {
        if (!isset($this->highClsMap)) {
            $this->highClsMap = $this->scanMap($this->entities);
        }

        if (!($r = array_search($clsName, $this->highClsMap))) {
            if (!\in_array($clsName, $this->entities)) {
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

    /**
     * Shortcut method to create entity object by high,
     * Entity must have public zero-less constructor.
     */
    public function createEntity(string $high)
    {
        $cls = $this->getEntityByHigh($high);

        return EntityUtils::create($cls);
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
