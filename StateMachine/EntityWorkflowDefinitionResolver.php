<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Symfony\Component\Workflow\Registry;
use Bungle\Framework\Entity\EntityUtils;
use Bungle\Framework\StateMachine\Vina;

class EntityWorkflowDefinitionResolver implements EntityWorkflowDefinitionResolverInterface
{
    private Registry $workflowRegistry;
    private Vina $vina;

    public function __construct(Registry $workflowRegistry, Vina $vina)
    {
        $this->workflowRegistry = $workflowRegistry;
        $this->vina = $vina;
    }

    public function resolveDefinition(string $entityClass): array
    {
        $subject = EntityUtils::create($entityClass);
        $wf = $this->workflowRegistry->get($subject);
        return [
          $wf->getDefinition(),
          $this->vina->getTransitionTitles($subject)
        ];
    }
}
