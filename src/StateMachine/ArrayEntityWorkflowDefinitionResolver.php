<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

final class ArrayEntityWorkflowDefinitionResolver implements EntityWorkflowDefinitionResolverInterface
{
    /**
     * @var array<class-string, array{\Symfony\Component\Workflow\Definition, array<string, string>}>
     */
    private array $defs;

    /**
     * @param array<class-string, array{\Symfony\Component\Workflow\Definition, array<string, string>}> $defs
     */
    public function __construct(array $defs)
    {
        $this->defs = $defs;
    }

    public function resolveDefinition(string $entityClass): array
    {
        return $this->defs[$entityClass];
    }
}
