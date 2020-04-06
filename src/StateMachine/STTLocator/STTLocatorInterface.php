<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine\STTLocator;

use Bungle\Framework\StateMachine\EventListener\AbstractSTT;

interface STTLocatorInterface
{
    /**
     * Return stt class for entity class.
     */
    public function getSTTForClass(string $entityClass): AbstractSTT;
}
