<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

/**
 * Interface that do the actual db operation.
 */
interface DBProviderInterface
{
    public function count(Query $q): int;

    // Should returns Traversable interface jb
    public function search(Query $q): iterable;
}
