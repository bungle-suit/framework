<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Symfony\Component\Workflow\Definition;

interface EntityWorkflowDefinitionResolverInterface
{
    /**
     * Resolve workflow definition by entity class.
     * [Definition, [TransitionName => ActionName]
     *
     * @return Definition[]|string[]
     */
    public function resolveDefinition(string $entityClass): array;
}
