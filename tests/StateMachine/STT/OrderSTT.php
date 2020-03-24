<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\STT;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Bungle\Framework\StateMachine\EventListener\AbstractSTT;
use Bungle\Framework\StateMachine\EventListener\STTInterface;
use Bungle\Framework\StateMachine\SaveStepContext;
use Bungle\Framework\StateMachine\StepContext;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use Bungle\Framework\Traits\HasAttributesInterface;

/** @SuppressWarnings(PHPMD.UnusedFormalParameter("ord")) */
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

    public function abort(Order $ord, StepContext $ctx): ?string
    {
        if ($ctx->get('abort', true)) {
            return 'Abort';
        }
        return null;
    }

    public function updateCodeWithTransitionName(Order $ord, StepContext $ctx): void
    {
        self::log($ctx, 'update');
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
    protected function steps(): array
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

    /**
     * {@inheritdoc}
     */
    protected function beforeSteps(): array
    {
        return [
          [static::class, 'hitBefore'],
          [static::class, 'prepBarAttr'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function afterSteps(): array
    {
        return [
          [static::class, 'hitAfter'],
          [static::class, 'saveLog'],
        ];
    }

    protected function saveSteps(): array
    {
        return [
          'saved' => [
            fn (Order $ord, SaveStepContext $ctx) => self::log($ctx, 'save'),
            fn (Order $ord) => $ord->name = 'foo',
            fn (Order $ord) => $ord->setState('hack'), // test prevent manipulate set state.
          ],
          StatefulInterface::INITIAL_STATE => [],
        ];
    }

    protected function beforeSaveSteps(): array
    {
        return [
          fn (Order $ord, SaveStepContext $ctx) => self::log($ctx, 'before save'.$ctx->get('attr', '')),
          fn (Order $ord) => $ord->before = 'bar',
        ];
    }

    protected function afterSaveSteps(): array
    {
        return [
          fn (Order $ord, SaveStepContext $ctx) => self::log($ctx, 'after save'),
          fn (Order $ord) => $ord->after = 'after',
          [self::class, 'saveLog'],
        ];
    }

    private static function log(HasAttributesInterface $ctx, string $msg): void
    {
        $s = $ctx->get('log', '');
        $s .= ($s ? ';' : '').$msg;
        $ctx->set('log', $s);
    }

    public static function getHigh(): string
    {
        return 'ord';
    }

    public static function prepBarAttr(Order $ord, StepContext $ctx): void
    {
        self::log($ctx, 'bar');
    }

    public static function hitBefore(Order $ord, StepContext $ctx): void
    {
        self::log($ctx, 'before');
    }

    public function hitAfter(Order $ord, StepContext $ctx): void
    {
        self::log($ctx, 'after');
    }

    public static function saveLog(Order $ord, HasAttributesInterface $ctx): void
    {
        $ord->log = $ctx->get('log');
    }
}
