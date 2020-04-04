<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Security;

use Bungle\Framework\Entity\ArrayEntityDiscovery;
use Bungle\Framework\Entity\ArrayEntityMetaResolver;
use Bungle\Framework\Entity\ArrayHighResolver;
use Bungle\Framework\Entity\EntityMeta;
use Bungle\Framework\Entity\EntityMetaResolverInterface;
use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\Security\EntityRoleDefinitionProvider;
use Bungle\Framework\Security\RoleDefinition;
use Bungle\Framework\StateMachine\ArrayEntityWorkflowDefinitionResolver as ArrayWorkflowResolver;
use Bungle\Framework\StateMachine\EntityWorkflowDefinitionResolverInterface;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use Bungle\Framework\Tests\StateMachine\Entity\Product;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;
use Symfony\Component\Workflow\Transition;
use function iterator_to_array;

final class EntityRoleDefinitionProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    private EntityMeta $productMeta;
    private EntityMeta $orderMeta;
    private EntityMetaResolverInterface $metaResolver;

    protected function setUp(): void
    {
        $this->productMeta = new EntityMeta(Product::class, 'Product', []);
        $this->orderMeta = new EntityMeta(Order::class, 'Order', []);
        $this->metaResolver = new ArrayEntityMetaResolver([
            Order::class => $this->orderMeta,
            Product::class => $this->productMeta,
        ]);
    }

    public function testRoles(): void
    {
        $orderDef = new Definition([
            'new', 'saved', 'checked', 'deleted',
        ], [
            new Transition('save', 'new', 'saved'),
            new Transition('check', 'saved', 'checked'),
            new Transition('rollback', 'checked', 'saved'),
            new Transition('delete', 'saved', 'deleted'),
        ]);
        $productDef = new Definition([
            'new', 'saved', 'checked', 'disabled',
        ], [
            new Transition('save', 'new', 'saved'),
            new Transition('check', 'saved', 'checked'),
            new Transition('rollback', 'checked', 'saved'),
            new Transition('disable', 'checked', 'disabled'),
            new Transition('enable', 'disabled', 'checked'),
            // If an transition has two from state, then Workflow passed into two Transition object.
            // maybe because we use StateMachine instead of Workflow.
            new Transition('rollback', 'disabled', 'saved'),
        ]);

        $workflowResolver = new ArrayWorkflowResolver([
            Order::class => [
                $orderDef,
                ['save' => 'Save', 'check' => 'Check'],
            ],
            Product::class => [$productDef, []],
        ]);
        $entityReg = new EntityRegistry(
            new ArrayEntityDiscovery([
                Order::class,
                Product::class,
            ]),
            new ArrayHighResolver([
                Order::class => 'ord',
                Product::class => 'prd',
            ]),
            $this->metaResolver
        );
        $entityRoleDefProvider = new EntityRoleDefinitionProvider($entityReg, $workflowResolver);

        self::assertEquals([
            new RoleDefinition('ROLE_ord_view', '查看', '', 'Order'),
            new RoleDefinition('ROLE_ord_save', 'Save', '', 'Order'),
            new RoleDefinition('ROLE_ord_check', 'Check', '', 'Order'),
            new RoleDefinition('ROLE_ord_rollback', 'rollback', '', 'Order'),
            new RoleDefinition('ROLE_ord_delete', 'delete', '', 'Order'),
            new RoleDefinition('ROLE_prd_view', '查看', '', 'Product'),
            new RoleDefinition('ROLE_prd_save', 'save', '', 'Product'),
            new RoleDefinition('ROLE_prd_check', 'check', '', 'Product'),
            new RoleDefinition('ROLE_prd_rollback', 'rollback', '', 'Product'),
            new RoleDefinition('ROLE_prd_disable', 'disable', '', 'Product'),
            new RoleDefinition('ROLE_prd_enable', 'enable', '', 'Product'),
        ], iterator_to_array($entityRoleDefProvider->getRoleDefinitions()));
    }

    public function testIgnoreNoWorkflowDefined(): void
    {
        $entityReg = new EntityRegistry(
            new ArrayEntityDiscovery([ Order::class ]),
            new ArrayHighResolver([ Order::class => 'ord' ]),
            new ArrayEntityMetaResolver([])
        );
        $workflowResolver = Mockery::mock(EntityWorkflowDefinitionResolverInterface::class);
        $workflowResolver->allows('resolveDefinition')
            ->andThrow(InvalidArgumentException::class);
        $entityRoleDefProvider = new EntityRoleDefinitionProvider($entityReg, $workflowResolver);
        self::assertEmpty(iterator_count($entityRoleDefProvider->getRoleDefinitions()));
    }
}
