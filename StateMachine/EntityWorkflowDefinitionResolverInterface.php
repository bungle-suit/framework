<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

interface EntityWorkflowDefinitionResolverInterface
{
    // Resolve workflow definition by entity class.
    // @return [Definition, [TransitionName => ActionName]
    public function resolveDefinition(string $entityClass): array;
}
