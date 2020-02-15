<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\STT;

use Bungle\Framework\StateMachine\StepContext;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use Bungle\Framework\StateMachine\EventListener\AbstractSTT;
use Bungle\Framework\StateMachine\EventListener\STTInterface;

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

    /**
     * @inheritDoc
     */
    protected function createSteps(): array
    {
        return [
          'save' => [
            [static::class, 'setCodeFoo'],
          ],
          'update' => [
            [static::class, 'updateCodeWithTransitionName'],
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
