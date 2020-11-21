<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

/**
 * Resolve high from an map(array).
 */
class ArrayHighResolver implements HighResolverInterface
{
    /**
     * @var array<class-string<mixed>, string>
     */
    private array $highs;

    /**
     * @param array<class-string<mixed>, string> $highs
     */
    public function __construct(array $highs)
    {
        $this->highs = $highs;
    }

    public function resolveHigh(string $entityCls): ?string
    {
        return $this->highs[$entityCls] ?? null;
    }
}
