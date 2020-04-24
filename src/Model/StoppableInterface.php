<?php
declare(strict_types=1);

namespace Bungle\Framework\Model;

/**
 * Object support stoppable behavior, implement this interface,
 * such as Step context object.
 *
 * Use @see Stoppable trait to implement.
 */
interface StoppableInterface
{
    /**
     * Set stop flag.
     */
    public function stop(): void;

    /**
     * Returns true if stop() was called.
     */
    public function isStopped(): bool;
}
