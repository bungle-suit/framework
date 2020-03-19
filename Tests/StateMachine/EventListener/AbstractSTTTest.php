<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\EventListener;

use Bungle\Framework\Exception\Exceptions;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use Bungle\Framework\Tests\StateMachine\STT\OrderSTT;
use Symfony\Component\Workflow\Exception\TransitionException;

final class AbstractSTTTest extends TestBase
{
    public function setUp(): void
    {
        parent::setUp();

        $listener = new OrderSTT();
        $this->dispatcher->addListener('workflow.transition', $listener);
    }

    public function testInvoke(): void
    {
        $this->sm->apply($this->ord, 'save');
        self::assertEquals('foo', $this->ord->code);
        self::assertEquals('saved', $this->ord->getState());
    }

    public function testInvokeAbort(): void
    {
        $this->expectException(TransitionException::class);
        $this->ord->setState('saved');
        $this->sm->apply($this->ord, 'check');
    }

    public function testInvokeWithContext(): void
    {
        $this->ord->setState('saved');
        $this->sm->apply($this->ord, 'update');
        self::assertEquals('update', $this->ord->code);
        self::assertEquals('saved', $this->ord->getState());

        self::assertEquals('saved', $this->ord->fromState);
        self::assertEquals('saved', $this->ord->toState);
        self::assertEquals('update', $this->ord->transition->getName());
        self::assertEquals('update', $this->ord->transitionName);
        self::assertSame($this->sm, $this->ord->workflow);
    }

    public function testIgnoreStepsNotConfigured(): void
    {
        $this->expectExceptionObject(Exceptions::notSetupStateMachineSteps(Order::class, 'print'));

        $this->ord->setState('saved');
        $this->sm->apply($this->ord, 'print');
        self::assertEquals('saved', $this->ord->getState());
    }

    public function testBeforeAfter(): void
    {
        $this->sm->apply($this->ord, 'save');
        self::assertEquals('before;bar;after', $this->ord->log);

        $this->ord->setState('saved');
        $this->sm->apply($this->ord, 'update');
        self::assertEquals('before;bar;update;after', $this->ord->log);
    }
}
