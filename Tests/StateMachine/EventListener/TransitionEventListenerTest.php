<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\EventListener;

use Bungle\Framework\StateMachine\EventListener\TransitionEventListener;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\Entity\ArrayEntityDiscovery;
use Bungle\Framework\Entity\ArrayHighResolver;

final class TransitionEventListenerTest extends TestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $reg = new EntityRegistry(
            new ArrayEntityDiscovery([Order::class]),
            new ArrayHighResolver([Order::class=> 'ord']),
        );

        $listener = new TransitionEventListener($reg);
        $this->dispatcher->addListener('workflow.transition', $listener);
    }

    public function testGetSTTClass(): void
    {
        $f = TransitionEventListener::class.'::getSTTClass';

        self::assertEquals('STT\FooSTT', $f('Entity\Foo'));
        self::assertEquals('Order\STT\BarSTT', $f('Order\Entity\Bar'));
    }

    public function testInvoke(): void
    {
        $this->sm->apply($this->ord, 'save');
        self::assertEquals('foo', $this->ord->code);
        self::assertEquals('saved', $this->ord->state);
    }

    public function testInvokeWithContext(): void
    {
        $this->ord->state = 'saved';
        $this->sm->apply($this->ord, 'update');
        self::assertEquals('update', $this->ord->code);
        self::assertEquals('saved', $this->ord->state);
    }

    public function testIgnoreStepsNotConfigured(): void
    {
        self::expectWarning();
        $this->ord->state = 'saved';
        $this->sm->apply($this->ord, 'print');
        self::assertEquals('saved', $this->ord->state);
    }
}
