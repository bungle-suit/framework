<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine\STTLocator;

use Bungle\Framework\StateMachine\STT\AbstractSTT;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerSTTLocator implements STTLocatorInterface, ContainerAwareInterface
{
    private ?ContainerInterface $container = null;

    public function getSTTForClass(string $entityClass): AbstractSTT
    {
        if (!$this->container) {
            throw new LogicException('No DI Container attached.') ;
        }

        $words = explode('\\', $entityClass);
        $words[1] = 'STT';
        $sttClass = implode('\\', $words).'Service';
        /** @var AbstractSTT $r */
        $r = $this->container->get($sttClass);
        return $r;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }
}
