<?php
declare(strict_types=1);

namespace Bungle\Framework\Entity;

/**
 * Resolve high from an map(array).
 */
class ArrayHighResolver implements HighResolverInterface
{
    private array $highs;

    public function __construct(array $highs)
    {
        $this->highs = $highs;
    }

    public function resolveHigh(string $entityCls): ?string
    {
        return $this->highs[$entityCls] ?? null;
    }
}
