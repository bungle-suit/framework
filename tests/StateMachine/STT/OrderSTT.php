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

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter("ord"))
 * @extends AbstractSTT<Order>
 */
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

    /**
     * @return void|string
     */
    public function abort(Order $ord, StepContext $ctx)
    {
        if ($ctx->get('abort', true)) {
            return 'Abort';
        }
    }

    public static function updateCodeWithTransitionName(Order $ord, StepContext $ctx): void
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

    protected function steps(): array
    {
       return [
            'actions' => [
                'save' => [
                    [self::class, 'setCodeFoo'],
                ],
                'update' => [
                    [self::class, 'updateCodeWithTransitionName'],
                    [self::class, 'saveContextAttrs'],
                ],
                'check' => [
                    [self::class, 'setCodeBar'],
                    [$this, 'abort'],
                    [self::class, 'setCodeFoo'],
                ],
            ],
           'before' => [
               [self::class, 'hitBefore'],
               [self::class, 'prepBarAttr'],
           ],
           'after' => [
               [self::class, 'hitAfter'],
               [self::class, 'saveLog'],
           ],

           'saveActions' => [
               'saved' => [
                   function (Order $ord, SaveStepContext $ctx): void {
                       self::log($ctx, 'save');
                   },
                   function (Order $ord): void {
                       $ord->name = 'foo';
                   },
                   function (Order $ord): void {
                       $ord->setState('hack');
                   }, // test prevent manipulate set state.
               ],
               StatefulInterface::INITIAL_STATE => [],
           ],
           'beforeSave' => [
               function (Order $ord, SaveStepContext $ctx): void {
                   self::log($ctx, 'before save'.$ctx->get('attr', ''));
               },
               function (Order $ord): void {
                   $ord->before = 'bar';
               },
           ],
           'afterSave' => [
               function (Order $ord, SaveStepContext $ctx): void {
                   self::log($ctx, 'after save');
               },
               function (Order $ord): void {
                   $ord->after = 'after';
               },
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

    public static function hitAfter(Order $ord, StepContext $ctx): void
    {
        self::log($ctx, 'after');
    }

    public static function saveLog(Order $ord, HasAttributesInterface $ctx): void
    {
        $ord->log = $ctx->get('log');
    }
}
