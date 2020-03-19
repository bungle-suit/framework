<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\WorkflowInterface;
use Bungle\Framework\Traits\Attributes;

final class StepContext
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

    /**
     * Returns true if the attribute exist.
     */
    public function has(string $name): bool
    {
        return $this->getAttributeBag()->has($name);
    }

    /**
     * Returns attribute value, returns default if not exist.
     */
    public function get(string $name, $default = null)
    {
        return $this->getAttributeBag()->get($name, $default);
    }

    /**
     * Set attribute.
     */
    public function set(string $name, $value)
    {
        $this->getAttributeBag()->set($name, $value);
    }

    /**
     * Returns all attributes.
     */
    public function all(): array
    {
    }
}
