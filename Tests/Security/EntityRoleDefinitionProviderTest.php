<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Security;

use PHPUnit\Framework\TestCase;
use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\Entity\ArrayEntityDiscovery;
use Bungle\Framework\Entity\ArrayHighResolver;
use Bungle\Framework\Security\EntityRoleDefinitionProvider;
use Bungle\Framework\Security\RoleDefinition;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use Bungle\Framework\Tests\StateMachine\Entity\Product;
use Bungle\Framework\StateMachine\ArrayEntityWorkflowDefinitionResolver as ArrayWorkflowResolver;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\Transition;

final class EntityRoleDefinitionProviderTest extends TestCase
{
    public function testRoles(): void
    {
        $orderDef = new Definition([
        'new', 'saved', 'checked', 'deleted'
        ], [
        new Transition('save', 'new', 'saved'),
        new Transition('check', 'saved', 'checked'),
        new Transition('rollback', 'checked', 'saved'),
        new Transition('delete', 'saved', 'deleted'),
        ]);
        $productDef = new Definition([
        'new', 'saved', 'checked', 'disabled'
        ], [
        new Transition('save', 'new', 'saved'),
        new Transition('check', 'saved', 'checked'),
        new Transition('rollback', 'checked', 'saved'),
        new Transition('disable', 'checked', 'disabled'),
        new Transition('enable', 'disabled', 'checked'),
        ]);

        $workflowResolver = new ArrayWorkflowResolver([
        Order::class => [
        $orderDef,
        ['save' => 'Save', 'check' => 'Check'],
        ],
        Product::class => [$productDef, []]
        ]);
        $entityReg = new EntityRegistry(
            new ArrayEntityDiscovery([
            Order::class,
            Product::class,
            ]),
            new ArrayHighResolver([
            Order::class => 'ord',
            Product::class => 'prd',
            ])
        );
        $entityRoleDefProvider = new EntityRoleDefinitionProvider($entityReg, $workflowResolver);

        self::assertEquals([
        new RoleDefinition('ROLE_ord_save', 'Save', ''),
        new RoleDefinition('ROLE_ord_check', 'Check', ''),
        new RoleDefinition('ROLE_ord_rollback', 'rollback', ''),
        new RoleDefinition('ROLE_ord_delete', 'delete', ''),
        new RoleDefinition('ROLE_prd_save', 'save', ''),
        new RoleDefinition('ROLE_prd_check', 'check', ''),
        new RoleDefinition('ROLE_prd_rollback', 'rollback', ''),
        new RoleDefinition('ROLE_prd_disable', 'disable', ''),
        new RoleDefinition('ROLE_prd_enable', 'enable', ''),
        ], \iterator_to_array($entityRoleDefProvider->getRoleDefinitions()));
    }
}
