<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\Tree;

use Doctrine\Common\Collections\Collection;

/**
 * Tree item that use children property to store tree relationship.
 *
 * @template T of ChildrenTreeNode
 */
interface ChildrenTreeNode extends ParentTreeNode
{
    /**
     * @phpstan-return Collection<T>
     */
    public function getChildren(): Collection;

    /**
     * @phpstan-param T
     */
    public function addChild(self $child): void;
}
