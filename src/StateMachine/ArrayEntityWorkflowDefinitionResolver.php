<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

final class ArrayEntityWorkflowDefinitionResolver implements EntityWorkflowDefinitionResolverInterface
{
    private array $defs;

    public function __construct(array $defs)
    {
        $this->defs = $defs;
    }

    public function resolveDefinition(string $entityClass): array
    {
        return $this->defs[$entityClass];
    }
}
