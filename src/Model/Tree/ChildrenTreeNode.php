<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\Tree;

use Doctrine\Common\Collections\Collection;

/**
 * Tree item that use children property to store tree relationship.
 *
 * @template T
 */
interface ChildrenTreeNode extends ParentTreeNode
{
    /**
     * @phpstan-return Collection<int, T>
     */
    public function getChildren(): Collection;

    /**
     * @phpstan-param T&ChildrenTreeNode $child
     */
    public function addChild(self $child): void;
}
