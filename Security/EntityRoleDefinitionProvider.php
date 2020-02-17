<?php

declare(strict_types=1);

namespace Bungle\Framework\Security;

use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\StateMachine\EntityWorkflowDefinitionResolverInterface as WorkflowResolver;

final class EntityRoleDefinitionProvider implements RoleDefinitionProviderInterface
{
    private EntityRegistry $entityRegistry;
    private WorkflowResolver $workflowResolver;

    public function __construct(EntityRegistry $entityRegistry, WorkflowResolver $workflowResolver)
    {
        $this->entityRegistry = $entityRegistry;
        $this->workflowResolver = $workflowResolver;
    }

    public function getRoleDefinitions(): iterable
    {
        foreach ($this->entityRegistry->entities as $entity) {
            $high = $this->entityRegistry->getHigh($entity);
            list($def, $actionTitles) = $this->workflowResolver->resolveDefinition($entity);
            foreach ($def->getTransitions() as $trans) {
                $action = $trans->getName();
                yield new RoleDefinition(
                    RoleDefinition::newActionRole($high, $action),
                    $actionTitles[$action] ?? $action,
                    '',
                );
            }
        }
    }
}
