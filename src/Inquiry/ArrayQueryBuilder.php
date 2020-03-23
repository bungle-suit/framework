<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

/**
 * Simple QueryBuilderInterface implementation, useful in
 * unit tests.
 */
class ArrayQueryBuilder implements QueryBuilderInterface
{
    private array $steps;

    public function __construct(array $steps)
    {
        $this->steps = $steps;
    }

    public function steps(): array
    {
        return $this->steps;
    }
}
