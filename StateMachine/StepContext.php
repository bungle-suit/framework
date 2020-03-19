<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Bungle\Framework\Traits\Attributes;
use Bungle\Framework\Traits\HasAttributesInterface;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\WorkflowInterface;

final class StepContext implements HasAttributesInterface
{
    use Attributes;

    private $transition;
    private $workflow;

    public function __construct(WorkflowInterface $workflow, Transition $transition)
    {
        $this->workflow = $workflow;
        $this->transition = $transition;
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
