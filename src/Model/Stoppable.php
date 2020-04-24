<?php
declare(strict_types=1);

namespace Bungle\Framework\Model;

/**
 * Trait to implement StoppableInterface
 */
trait Stoppable
{
    private bool $isStopped = false;

    public function stop(): void
    {
        $this->isStopped = true;
    }

    public function isStopped(): bool
    {
        return $this->isStopped;
    }
}
