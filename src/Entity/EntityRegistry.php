<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

use Bungle\Framework\Exceptions;

use function in_array;

class EntityRegistry
{
    // array of entities full class name.
    /** @phpstan-var class-string<mixed>[] $entities */
    private array $entities;
    private HighResolverInterface $highResolver;
    /** @var array<string, class-string<mixed>> */
    private array $highClsMap;

    private EntityDiscovererInterface $discoverer;

    public function __construct(
        EntityDiscovererInterface $discoverer,
        HighResolverInterface $highResolver
    ) {
        $this->highResolver = $highResolver;
        $this->discoverer = $discoverer;
    }

    /**
     * Like getHigh(), return empty if not High defined on
     * $clsName instead of throw exception.
     * @param class-string<mixed> $clsName
     */
    public function getHighSafe(string $clsName): string
    {
        if (!isset($this->highClsMap)) {
            $this->highClsMap = $this->scanMap($this->getEntities());
        }

        $r = array_search($clsName, $this->highClsMap);
        if ($r === false) {
            return '';
        }
        if (!in_array($clsName, $this->getEntities())) {
            return '';
        }

        return $r;
    }

    /**
     * Get high prefix by clsName.
     * @param class-string<mixed> $clsName
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
     * @return class-string<mixed>
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
     * @return mixed
     */
    public function createEntity(string $high)
    {
        $cls = $this->getEntityByHigh($high);

        return EntityUtils::create($cls);
    }

    /**
     * @param array<class-string<mixed>> $entities
     * @return array<string, class-string<mixed>>
     */
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

    /**
     * @return array<class-string<mixed>>
     */
    public function getEntities(): array
    {
        if (!isset($this->entities)) {
            $this->entities = iterator_to_array($this->discoverer->getAllEntities(), false);
        }

        return $this->entities;
    }
}
