<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\Tree;

use Bungle\Framework\Entity\CommonTraits\NameAbleInterface;
use Bungle\Framework\Model\Tree\ChildrenTreeNode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class TestChildrenNode implements ChildrenTreeNode, NameAbleInterface
{
    private Collection $children;
    private string $name;
    private ?self $parent;

    /**
     * @param self[] $children
     */
    public function __construct(string $name, array $children = [], ?self $parent = null)
    {
        $this->name = $name;
        $this->children = new ArrayCollection();
        $this->parent = $parent;

        foreach ($children as $child) {
            $this->children->add($child);
            $child->parent = $this;
        }
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param self $child
     */
    public function addChild(ChildrenTreeNode $child): void
    {
        $this->children->add($child);
        $child->parent = $this;
    }

    /**
     * @param array<string, string[]> $arr
     */
    public static function createTree(string $rootName, array $arr): self
    {
        $root = new self($rootName);

        foreach (self::createForest($arr) as $childNode) {
            $root->addChild($childNode);
        }

        return $root;
    }

    /**
     * @param array<string, string[]> $arr
     * @return self[]
     */
    private static function createForest(array $arr): array
    {
        $r = [];
        foreach ($arr as $name => $children) {
            if (is_string($name)) {
                $r[] = self::createTree($name, $children);
            } else {
                $r[] = new self($children);
            }
        }

        return $r;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
