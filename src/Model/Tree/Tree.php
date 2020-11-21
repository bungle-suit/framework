<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\Tree;

use Bungle\Framework\Entity\CommonTraits\NameAbleInterface;
use LogicException;
use Traversable;

class Tree
{
    /**
     * Convert parent based tree to children based forest, if the tree
     * has only single root, use @see self::toTree().
     *
     * @template T of ChildrenTreeNode
     * @template V of ParentTreeNode
     * @phpstan-param V[] $items
     * @phpstan-param callable(V): T $fCreateNode create dest tree node from source.
     * @phpstan-return T[]
     */
    public static function toForest(array $items, callable $fCreateNode): array
    {
        $map = [];
        $findOrCreate = function (ParentTreeNode $node) use (
            $fCreateNode,
            &$map
        ) : ChildrenTreeNode {
            $id = spl_object_id($node);
            if (!isset($map[$id])) {
                $map[$id] = $fCreateNode($node);
            }

            return $map[$id];
        };

        $r = [];
        foreach ($items as $item) {
            $child = $findOrCreate($item);
            if ($item->getParent() === null) {
                $r[] = $child;

                continue;
            }
            $parent = $findOrCreate($item->getParent());
            $parent->addChild($child);
        }

        /** @phpstan-var T[] */
        return $r;
    }

    /**
     * @template T of ChildrenTreeNode
     * @template V of ParentTreeNode
     * @phpstan-param array<V> $items
     * @phpstan-param callable(V): T $fCreateNode create dest tree node from source.
     * @phpstan-return T
     * @throws LogicException if $items is empty, or more than one root node.
     */
    public static function toTree(array $items, callable $fCreateNode): ?ChildrenTreeNode
    {
        $roots = self::toForest($items, $fCreateNode);
        if (!$roots) {
            return null;
        }

        if (count($roots) !== 1) {
            throw new LogicException('More than one root node');
        }

        return $roots[0];
    }

    /**
     * @template T of ParentTreeNode
     * @phpstan-param T $node
     * @phpstan-return Traversable<T>
     */
    public static function iterToRoot(ParentTreeNode $node): Traversable
    {
        for ($n = $node; $n !== null; $n = $n->getParent()) {
            yield $n;
        }
    }

    /**
     * Iterate all descent nodes of $node.
     * @template T
     * @phpstan-param ChildrenTreeNode<T> $node
     * @phpstan-return Traversable<T>
     */
    public static function iterDescent(ChildrenTreeNode $node): Traversable
    {
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        /** @phpstan-var T */
        $n = $node;
        yield $n;
        foreach ($node->getChildren() as $child) {
            yield from self::iterDescent($child);
        }
    }

    /**
     * Return path of the node from root, path is names from root to $node separate with '/'.
     * Not prefixed with '/', i.e., 'a/b/c' instead of '/a/b/c'.
     *
     * @param ParentTreeNode|NameAbleInterface $node
     * @param bool $includeRoot exclude root name if false.
     * @phpstan-param ParentTreeNode&NameAbleInterface $node
     */
    public static function path(ParentTreeNode $node, bool $includeRoot = true): string
    {
        $parts = [];
        /** @var NameAbleInterface|ParentTreeNode $node */
        foreach (self::iterToRoot($node) as $node) {
            $parts[] = $node->getName();
        }
        $parts = array_reverse($parts, false);
        if (!$includeRoot) {
            array_shift($parts);
        }

        return implode('/', $parts);
    }
}
