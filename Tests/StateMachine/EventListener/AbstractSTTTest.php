<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\EventListener;

use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\Entity\ArrayEntityDiscovery;
use Bungle\Framework\Entity\ArrayHighResolver;
use Bungle\Framework\Tests\StateMachine\STT\OrderSTT;
use Bungle\Framework\Tests\StateMachine\Entity\Order;

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
