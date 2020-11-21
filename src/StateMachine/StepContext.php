<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Bungle\Framework\Model\HasAttributes;
use Bungle\Framework\Model\HasAttributesInterface;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\WorkflowInterface;

final class StepContext implements HasAttributesInterface
{
    use HasAttributes;

    private Transition $transition;
    private WorkflowInterface $workflow;

    /**
     * StepContext constructor.
     * @param WorkflowInterface $workflow
     * @param Transition $transition
     * @param array<string, mixed> $initialAttrs Initial attributes of StepContext.
     */
    public function __construct(WorkflowInterface $workflow, Transition $transition, array $initialAttrs = [])
    {
        $this->workflow = $workflow;
        $this->transition = $transition;

        if ($initialAttrs) {
            $this->attributes = $initialAttrs;
        }
    }

    public function getTransition(): Transition
    {
        return $this->transition;
    }

    public function getWorkflow(): WorkflowInterface
    {
        return $this->workflow;
    }

    public function getTransitionName(): string
    {
        return $this->transition->getName();
    }

    public function getFromState(): string
    {
        return $this->transition->getFroms()[0];
    }

    public function getToState(): string
    {
        return $this->transition->getTos()[0];
    }
}
