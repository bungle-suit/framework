<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine\EventListener;

use Bungle\Framework\Exception\Exceptions;
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
                throw new TransitionException($subject, $ctx->getTransitionName(), $ctx->getWorkflow(), $msg);
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
            throw Exceptions::notSetupStateMachineSteps($cls, $actionName);
        }

        return $this->steps[$actionName];
    }

    /**
     * Sub class create steps array.
     */
    abstract protected function createSteps(): array;
}
