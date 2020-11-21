<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Bungle\Framework\Entity\EntityUtils;
use Symfony\Component\Workflow\Registry;

class EntityWorkflowDefinitionResolver implements EntityWorkflowDefinitionResolverInterface
{
    private Registry $workflowRegistry;
    private Vina $vina;

    public function __construct(Registry $workflowRegistry, Vina $vina)
    {
        $this->workflowRegistry = $workflowRegistry;
        $this->vina = $vina;
    }

    /**
     * @phpstan-param class-string<\Bungle\Framework\Entity\CommonTraits\StatefulInterface> $entityClass
     */
    public function resolveDefinition(string $entityClass): array
    {
        $subject = EntityUtils::create($entityClass);
        $wf = $this->workflowRegistry->get($subject);

        return [
          $wf->getDefinition(),
          $this->vina->getTransitionTitles($subject),
        ];
    }
}
