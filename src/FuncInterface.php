<?php

declare(strict_types=1);

namespace Bungle\Framework;

/**
 * Generic function type, useful such as unit tests to create mocked
 * functions.
 */
interface FuncInterface
{
    /**
     * @param mixed[] $args
     * @return mixed
     */
    public function __invoke(...$args);
}
