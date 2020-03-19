<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine\EventListener;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Bungle\Framework\Exception\Exceptions;
use Bungle\Framework\StateMachine\SaveStepContext;
use Bungle\Framework\StateMachine\StepContext;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\Workflow\Exception\TransitionException;

/**
 * Base class for STT services.
 *
 * Sub class must also implement STTInterface.
 *
 * TODO: remove fowlliing comment if Symfony/Flex implemented to auto
 * update config/services.yaml file.
 *
 * Recommend config/services.yaml to make STT services work:
 *
 *   # config/services.yml
 *   services:
 *       _defaults:
 *           autowire: true
 *
 *       _instanceof:
 *           Bungle\Framework\StateMachine\EventListener\AbstractSTT:
 *               tags: ['bungle.stt']
 *               public: true # TODO: maybe not needed, because it'll auto tagged as event subscriber
 *
 * Or use location based, maybe more applicable:
 *   # config/services.yml
 *   services:
 *       _defaults:
 *           autowire: true
 *
 *       App\STT\:
 *           resource: '../src/STT'
 *           tags: ['bungle.stt']
 *           public: true # TODO: maybe not needed, because it'll auto tagged as event subscriber
 */
abstract class AbstractSTT
{
    final public function __invoke(TransitionEvent $event): void
    {
        $ctx = new StepContext($event->getWorkflow(), $event->getTransition());
        $subject = $event->getSubject();
        $action = $event->getTransition()->getName();
        foreach ($this->getTransitionSteps($subject, $action) as $step) {
            $msg = call_user_func($step, $subject, $ctx);
            if (is_string($msg)) {
                throw new TransitionException($subject, $ctx->getTransitionName(), $ctx->getWorkflow(), $msg);
            }
        }
    }

    /**
     * Sub class create steps array.
     */
    abstract protected function steps(): array;

    private function getTransitionSteps($subject, string $actionName)
    {
        yield from $this->beforeSteps();
        $steps = $this->steps();
        if (!isset($steps[$actionName])) {
            $cls = get_class($subject);
            throw Exceptions::notSetupStateMachineSteps($cls, $actionName);
        }
        yield from $steps[$actionName];
        yield from $this->afterSteps();
    }

    /**
     * After steps run after normal steps, runs for every transitions.
     */
    protected function afterSteps(): array
    {
        return [];
    }

    /**
     * Before steps run before normal steps, runs for every transitions.
     */
    protected function beforeSteps(): array
    {
        return [];
    }

    /**
     * Returns steps ran during edit/save action.
     *
     * Returns array like:
     *
     *   [
     *      'saved': [[$this, 'act1'], [$this, 'checkFoo']],
     *      'checked': [],
     *   ]
     *
     * If state not exist in returned array, then the save action not
     * enabled for it. Set empty array to enable save if no save action
     * needed.
     */
    protected function saveSteps(): array
    {
        return [];
    }

    /**
     * Steps run before all save steps.
     */
    protected function beforeSaveSteps(): array
    {
        return [];
    }

    /**
     * Steps run after all save steps.
     */
    protected function afterSaveSteps(): array
    {
        return [];
    }

    /**
     * Execute save action.
     */
    final public function invokeSave(StatefulInterface $entity): void
    {
        $ctx = new SaveStepContext();
        $state = $entity->getState();
        try {
            foreach ($this->getSaveSteps($entity) as $step) {
                $step($entity, $ctx);
            }
        } finally {
            // Prevent save step to manipulate state.
            $entity->setState($state);
        }
    }

    /**
     * Returns true if entity current state defines save steps.
     */
    public function canSave(StatefulInterface $entity): bool
    {
        $state = $entity->getState();

        return isset($this->saveSteps()[$state]);
    }

    private function getSaveSteps(StatefulInterface $entity)
    {
        $curState = $entity->getState();
        if (!$this->canSave($entity)) {
            $cls = get_class($entity);
            trigger_error("Try to execute save action on $cls state: $curState, which is not configured.");

            return;
        }

        yield from $this->beforeSaveSteps();
        yield from $this->saveSteps()[$curState] ?? null;
        yield from $this->afterSaveSteps();
    }
}
