<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine\STTLocator;

use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\StateMachine\STT\AbstractSTT;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class STTLocator implements STTLocatorInterface
{
    /** @var array<string, class-string<mixed>> */
    private array $sttClassesByHigh;
    private ContainerInterface $container;
    private EntityRegistry $entityRegistry;

    /**
     * @phpstan-param array<string, class-string<mixed>> $sttClassesByHigh
     */
    public function __construct(
        ContainerInterface $container,
        EntityRegistry $entityRegistry,
        array $sttClassesByHigh
    ) {
        $this->sttClassesByHigh = $sttClassesByHigh;
        $this->container = $container;
        $this->entityRegistry = $entityRegistry;
    }

    /**
     * @template T
     * @phpstan-return AbstractSTT<T>
     */
    public function getSTTForClass(string $entityClass): AbstractSTT
    {
        $high = $this->entityRegistry->getHigh($entityClass);
        if (!isset($this->sttClassesByHigh[$high])) {
            throw new LogicException("STT service for $entityClass not found");
        }

        /** @phpstan-var AbstractSTT<T> $r */
        // No need to check result is AbstractSTT, $sttClassesByHigh array is scanned from AbstractSTT.
        $r = $this->container->get($this->sttClassesByHigh[$high]);

        return $r;
    }
}
