<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine\STTLocator;

use Bungle\Framework\StateMachine\STT\AbstractSTT;

interface STTLocatorInterface
{
    /**
     * @template T
     * Return stt class for entity class.
     * @phpstan-param class-string<T> $entityClass
     * @phpstan-return AbstractSTT<T>
     */
    public function getSTTForClass(string $entityClass): AbstractSTT;
}
