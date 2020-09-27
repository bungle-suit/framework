<?php

declare(strict_types=1);

namespace Bungle\Framework\Ent\Code;

/**
 * A inc-ed prefixed unique name generator.
 */
class UniqueName
{
    private string $prefix;
    private int $idx;

    public function __construct(string $prefix, int $start = 1)
    {
        $this->prefix = $prefix;
        $this->idx = $start;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Return a new name.
     */
    public function next(): string
    {
        return $this->prefix.($this->idx++);
    }
}
