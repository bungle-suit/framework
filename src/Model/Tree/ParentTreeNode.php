<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\Tree;

/**
 * Tree item that use parent property to store tree relationship.
 *
 * @template T of ParentTreeItem
 */
interface ParentTreeNode
{
    /**
     * @phpstan-return T|null
     */
    public function getParent(): ?self;
}
