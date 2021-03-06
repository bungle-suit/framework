<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

interface EntityWorkflowDefinitionResolverInterface
{
    /**
     * Resolve workflow definition by entity class.
     * [Definition, [TransitionName => ActionName]
     *
     * @phpstan-param class-string<mixed> $entityClass
     * @return array{\Symfony\Component\Workflow\Definition, array<string, string>}
     */
    public function resolveDefinition(string $entityClass): array;
}
