<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine\STTLocator;

use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\StateMachine\STT\AbstractSTT;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class STTLocator implements STTLocatorInterface
{
    private array $sttClassesByHigh;
    private ContainerInterface $container;
    private EntityRegistry $entityRegistry;

    public function __construct(ContainerInterface $container, EntityRegistry $entityRegistry, array $sttClassesByHigh)
    {
        $this->sttClassesByHigh = $sttClassesByHigh;
        $this->container = $container;
        $this->entityRegistry = $entityRegistry;
    }

    public function getSTTForClass(string $entityClass): AbstractSTT
    {
        $high = $this->entityRegistry->getHigh($entityClass);
        if (!isset($this->sttClassesByHigh[$high])) {
            throw new LogicException("STT service for $entityClass not found") ;
        }

        /** @var AbstractSTT $r */
        // No need to check result is AbstractSTT, $sttClassesByHigh array is scanned from AbstractSTT.
        $r = $this->container->get($this->sttClassesByHigh[$high]);
        return $r;
    }
}
