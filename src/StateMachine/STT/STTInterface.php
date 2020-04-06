<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine\STT;

/**
 * Interface must implemented by the solid STT class.
 */
interface STTInterface
{
    /**
     * Returns the high of the entity the STT work for.
     */
    public static function getHigh(): string;
}
