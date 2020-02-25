<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\STT;

use Bungle\Framework\StateMachine\EventListener\AbstractSTT;
use Bungle\Framework\StateMachine\EventListener\STTInterface;
use Bungle\Framework\StateMachine\StepContext;
use Bungle\Framework\Tests\StateMachine\Entity\Order;

final class OrderSTT extends AbstractSTT implements STTInterface
{
    public static function setCodeFoo(Order $ord): void
    {
        $ord->code = 'foo';
    }

    public static function setCodeBar(Order $ord): void
    {
        $ord->code = 'bar';
    }

    public function abort(): string
    {
        return 'Abort';
    }

    public function updateCodeWithTransitionName(Order $ord, StepContext $ctx): void
    {
        $ord->code = $ctx->getTransitionName();
    }

    public static function getHighPrefix(): string
    {
        return 'ord';
    }

    public static function saveContextAttrs(Order $ord, StepContext $ctx): void
    {
        $ord->transition = $ctx->getTransition();
        $ord->transitionName = $ctx->getTransitionName();
        $ord->fromState = $ctx->getFromState();
        $ord->toState = $ctx->getToState();
        $ord->workflow = $ctx->getWorkflow();
    }

    /**
     * {@inheritdoc}
     */
    protected function createSteps(): array
    {
        return [
          'save' => [
            [static::class, 'setCodeFoo'],
          ],
          'update' => [
            [static::class, 'updateCodeWithTransitionName'],
            [static::class, 'saveContextAttrs'],
          ],
          'check' => [
            [static::class, 'setCodeBar'],
            [$this, 'abort'],
            [static::class, 'setCodeFoo'],
          ],
        ];
    }

    public static function getHigh(): string
    {
        return 'ord';
    }
}
