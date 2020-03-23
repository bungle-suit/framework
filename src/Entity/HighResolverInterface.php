<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

/**
 * Interface that resolves high value of entity class.
 */
interface HighResolverInterface
{
    /**
     * Returns null if high not defined in that entity class.
     */
    public function resolveHigh(string $entityCls): ?string;
}
