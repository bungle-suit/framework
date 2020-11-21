<?php
/** @noinspection PhpUndefinedFieldInspection */
/** @noinspection PhpUnused */
/** @noinspection PhpUnusedParameterInspection */

declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\STT;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Bungle\Framework\Model\HasAttributesInterface;
use Bungle\Framework\StateMachine\SaveStepContext;
use Bungle\Framework\StateMachine\StepContext;
use Bungle\Framework\StateMachine\STT\AbstractSTT;
use Bungle\Framework\StateMachine\STT\STTInterface;
use Bungle\Framework\Tests\StateMachine\Entity\Order;

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
     * @inheritDoc
     */
    protected function steps(): array
    {
       return [
            'actions' => [
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
            ],
           'before' => [
               [static::class, 'hitBefore'],
               [static::class, 'prepBarAttr'],
           ],
           'after' => [
               [static::class, 'hitAfter'],
               [static::class, 'saveLog'],
           ],

           'saveActions' => [
               'saved' => [
                   function (Order $ord, SaveStepContext $ctx) {
                       self::log($ctx, 'save');
                   },
                   fn (Order $ord) => $ord->name = 'foo',
                   function (Order $ord) {
                       $ord->setState('hack');
                   }, // test prevent manipulate set state.
               ],
               StatefulInterface::INITIAL_STATE => [],
           ],
           'beforeSave' => [
               function (Order $ord, SaveStepContext $ctx) {
                   self::log($ctx, 'before save'.$ctx->get('attr', ''));
               },
               fn (Order $ord) => $ord->before = 'bar',
           ],
           'afterSave' => [
               function (Order $ord, SaveStepContext $ctx) {
                   self::log($ctx, 'after save');
               },
               fn (Order $ord) => $ord->after = 'after',
               [self::class, 'saveLog'],
           ],
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
