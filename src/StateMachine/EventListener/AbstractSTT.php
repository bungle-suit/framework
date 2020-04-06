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
 * TODO: remove fowling comment if Symfony/Flex implemented to auto
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
 */
abstract class AbstractSTT
{
    final public function __invoke(TransitionEvent $event): void
    {
        $ctx = new StepContext($event->getWorkflow(), $event->getTransition(), $event->getContext());
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
     * @return array of settings configuration, contains fowling items:
     *
     * 1. 'before', Steps run before transition actions
     * 1. 'after', Steps run after transition actions.
     * 1. 'actions', Actions of each transition.
     * 1. 'beforeSave', Save steps run before save actions.
     * 1. 'afterSave', Save steps run after save actions.
     * 1. 'saveActions', Save actions for each state.
     *
     * `actions`, contains steps for each transition action, such as:
     * [ 'save' => [$step1, $step2], 'commit' => [$step3], 'rollback' => [] ]
     *
     * Use an empty array if the transition needs zero steps.
     *
     * `saveActions`, contains save steps for each state, if not configured,
     * save action for that state is disabled. Use empty array if not steps required.
     */
    abstract protected function steps(): array;

    private function getTransitionSteps($subject, string $actionName)
    {
        $steps = $this->steps();
        yield from $steps['before']??[];
        $actions = $steps['actions'];
        if (!isset($actions[$actionName])) {
            $cls = get_class($subject);
            throw Exceptions::notSetupStateMachineSteps($cls, $actionName);
        }
        yield from $actions[$actionName];
        yield from $steps['after']?? [];
    }

    /**
     * Execute save action, Handles `vina.high.save` action.
     */
    public function save(StatefulInterface $entity, array $attrs = []): void
    {
        $ctx = new SaveStepContext($attrs);
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

    public function canSave(StatefulInterface $subject): bool
    {
        return $this->_canSave($subject);
    }

    /**
     * Returns true if entity current state defines save steps.
     */
    private function _canSave(StatefulInterface $entity): bool
    {
        $state = $entity->getState();
        $steps = $this->steps();
        return isset($steps['saveActions'][$state]);
    }

    private function getSaveSteps(StatefulInterface $entity)
    {
        $curState = $entity->getState();
        if (!$this->_canSave($entity)) {
            $cls = get_class($entity);
            trigger_error("Try to execute save action on $cls state: $curState, which is not configured.");

            return;
        }

        $steps = $this->steps();
        yield from $steps['beforeSave'] ?? [];
        yield from $steps['saveActions'][$curState];
        yield from $steps['afterSave'] ?? [];
    }
}
