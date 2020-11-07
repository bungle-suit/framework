<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\Tree;

use Bungle\Framework\Model\Tree\ParentTreeNode;

class TestParentNode implements ParentTreeNode
{
    private string $name;
    private ?self $parent;

    public function __construct(string $name, ?self $parent = null)
    {
        $this->name = $name;
        $this->parent = $parent;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
