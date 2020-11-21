<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\Tree;

/**
 * Tree item that use parent property to store tree relationship.
 */
interface ParentTreeNode
{
    /**
     * @return ?static
     */
    public function getParent(): ?self;
}
