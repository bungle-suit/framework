<?php

declare(strict_types=1);

namespace Bungle\Framework\Security;

use Bungle\Framework\Ent\ObjectName;
use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\StateMachine\EntityWorkflowDefinitionResolverInterface as WorkflowResolver;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;
use Traversable;

final class EntityRoleDefinitionProvider implements RoleDefinitionProviderInterface
{
    private EntityRegistry $entityRegistry;
    private WorkflowResolver $workflowResolver;
    private ObjectName $objectName;

    public function __construct(
        EntityRegistry $entityRegistry,
        WorkflowResolver $workflowResolver,
        ObjectName $objectName
    ) {
        $this->entityRegistry = $entityRegistry;
        $this->workflowResolver = $workflowResolver;
        $this->objectName = $objectName;
    }

    public function getRoleDefinitions(): Traversable
    {
        foreach ($this->entityRegistry->getEntities() as $entity) {
            $high = $this->entityRegistry->getHigh($entity);
            /** @var Definition $def */
            /** @var string[] $actionTitles */
            try {
                [$def, $actionTitles] = $this->workflowResolver->resolveDefinition($entity);
            } catch (InvalidArgumentException $e) {
                // If workflow not defined, workflow registry throws thi exception.
                continue;
            }

            $actions = [];
            $group = $this->objectName->getName($entity);
            yield new RoleDefinition(
                RoleDefinition::newActionRole($high, 'view'),
                '查看',
                '',
                $group
            );
            foreach ($def->getTransitions() as $trans) {
                /** @var string $action */
                $action = $trans->getName();
                if (array_key_exists($action, $actions)) {
                    // If transition definition contains multiple from state, workflow
                    // parse into two Transition object with the same name.
                    //
                    // Maybe because we use StateMachine instead of Workflow, just
                    // guess.
                    continue;
                }

                $actions[$action] = true;
                yield new RoleDefinition(
                    RoleDefinition::newActionRole($high, $action),
                    $actionTitles[$action] ?? $action,
                    '',
                    $group
                );
            }
        }
    }
}
