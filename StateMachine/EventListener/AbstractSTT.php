<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine\EventListener;

use Bungle\Framework\Entity\EntityRegistry;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Bungle\Framework\StateMachine\StepContext;

abstract class AbstractSTT
{
    // Transaction name -> [step callbacks]
    protected array $steps;

    final public function __invoke(TransitionEvent $event): void
    {
        $ctx = new StepContext($event->getWorkflow(), $event->getTransition());
        $subject = $event->getSubject();
        $steps = $this->getSteps($subject, $event->getTransition()->getName());
        foreach ($steps as $step) {
            $msg = call_user_func($step, $subject, $ctx);
            if (is_string($msg)) {
                throw new TransitionException($msg);
            }
        }
    }

    private function getSteps(object $subject, string $actionName): array
    {
        if (!isset($this->steps)) {
            $this->steps = $this->createSteps();
        }

        if (!isset($this->steps[$actionName])) {
            $cls = get_class($subject);
            trigger_error(
                "StateMachine of $cls no transition $actionName",
                E_USER_WARNING
            );
            return [];
        }
        return $this->steps[$actionName];
    }

    /**
     * Sub class create steps array.
     */
    abstract protected function createSteps(): array;
}
