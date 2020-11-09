<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\Tree;

use Bungle\Framework\Model\Tree\Tree;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Traversable;

class TreeTest extends MockeryTestCase
{
    /**
     * @dataProvider toForestDataProvider
     * @param TestChildrenNode[] $exp
     * @param TestParentNode[] $nodes
     */
    public function testToForest(array $exp, array $nodes): void
    {
        self::assertEquals(
            $exp,
            Tree::toForest(
                $nodes,
                [self::class, 'createChildrenNode'],
            )
        );
    }

    /**
     * @return Traversable<mixed[]>
     */
    public function toForestDataProvider(): Traversable
    {
        // empty
        yield [[], []];

        $item0 = new TestParentNode('root');
        $itemA = new TestParentNode('a', $item0);
        $itemB = new TestParentNode('b', $item0);
        $itemA1 = new TestParentNode('1', $itemA);
        $tree2 = new TestParentNode('tree2');

        $expA1 = new TestChildrenNode('1');
        $expA = new TestChildrenNode('a', [$expA1]);
        $expB = new TestChildrenNode('b');
        yield [
            [
                new TestChildrenNode('root', [$expA, $expB]),
                new TestChildrenNode('tree2'),
            ],
            [$itemA, $itemB, $item0, $itemA1, $tree2],
        ];
    }

    /**
     * @dataProvider toTreeDataProvider
     * @param TestParentNode[] $nodes
     */
    public function testToTree(?TestChildrenNode $exp, array $items): void
    {
        self::assertEquals($exp, Tree::toTree($items, [self::class, 'createChildrenNode']));
    }

    /**
     * @return Traversable<mixed[]>
     */
    public function toTreeDataProvider(): Traversable
    {
        // empty
        yield [null, []];

        $item0 = new TestParentNode('root');
        $itemA = new TestParentNode('a', $item0);

        $expA = new TestChildrenNode('a');
        yield [
            new TestChildrenNode('root', [$expA]),
            [$itemA, $item0],
        ];
    }

    public static function createChildrenNode(TestParentNode $node): TestChildrenNode
    {
        return new TestChildrenNode($node->getName());
    }

    /**
     * @dataProvider iterToRootDataProvider
     * @param TestChildrenNode[] $exp
     */
    public function testIterToRoot(array $exp, TestChildrenNode $node): void
    {
        self::assertEquals($exp, iterator_to_array(Tree::iterToRoot($node), true));
    }

    /**
     * @return mixed[]
     */
    public function iterToRootDataProvider(): array
    {
        $root = TestChildrenNode::createTree('root', ['A' => ['1', '2'], 'B']);
        /** @var TestChildrenNode $nodeA */
        $nodeA = $root->getChildren()[0];
        /** @var TestChildrenNode $nodeA1 */
        $nodeA2 = $nodeA->getChildren()[1];

        return [
            [[$root], $root],
            [[$nodeA2, $nodeA, $root], $nodeA2],
        ];
    }

    /**
     * @dataProvider pathDataProvider
     */
    public function testPath(string $expIncludeRoot, string $expNoRoot, TestChildrenNode $node): void
    {
        self::assertEquals($expIncludeRoot, Tree::path($node));
        self::assertEquals($expNoRoot, Tree::path($node, false));
    }

    /**
     * @return array<array{string, string, TestChildrenNode}>
     */
    public function pathDataProvider(): array
    {
        $root = TestChildrenNode::createTree('root', ['A' => ['1', '2'], 'B']);
        /** @var TestChildrenNode $nodeA */
        $nodeA = $root->getChildren()[0];
        /** @var TestChildrenNode $nodeA1 */
        $nodeA2 = $nodeA->getChildren()[1];
        /** @var TestChildrenNode $nodeA1 */
        $nodeB = $root->getChildren()[1];

        return [
            ['root', '', $root],
            ['root/A/2', 'A/2', $nodeA2],
            ['root/B', 'B', $nodeB],
        ];
    }

    /**
     * @dataProvider iterDescentDataProvider
     * @param TestChildrenNode[] $exp
     */
    public function testIterDescent(array $exp): void
    {
        $node = $exp[0];
        self::assertEquals($exp, iterator_to_array(Tree::iterDescent($node), false));
    }

    /**
     * @return array<TestChildrenNode[]>
     */
    public function iterDescentDataProvider(): array
    {
        $root = TestChildrenNode::createTree('root', ['A' => ['1', '2'], 'B']);
        /** @var TestChildrenNode $nodeA */
        $nodeA = $root->getChildren()[0];
        /** @var TestChildrenNode $nodeA1 */
        $nodeA1 = $nodeA->getChildren()[0];
        /** @var TestChildrenNode $nodeA1 */
        $nodeA2 = $nodeA->getChildren()[1];
        /** @var TestChildrenNode $nodeA1 */
        $nodeB = $root->getChildren()[1];

        return [
            [[$nodeB]],
            [[$nodeA, $nodeA1, $nodeA2]],
            [[$root, $nodeA, $nodeA1, $nodeA2, $nodeB]],
        ];
    }
}
