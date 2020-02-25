<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\EventListener;

use Bungle\Framework\Tests\StateMachine\STT\OrderSTT;

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
        self::expectWarning();
        $this->ord->setState('saved');
        $this->sm->apply($this->ord, 'print');
        self::assertEquals('saved', $this->ord->getState());
    }
}
