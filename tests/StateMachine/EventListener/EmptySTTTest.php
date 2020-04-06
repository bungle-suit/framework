<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\EventListener;

use Bungle\Framework\Tests\StateMachine\STT\EmptySTT;

class EmptySTTTest extends TestBase
{
    public function setUp(): void
    {
        parent::setUp();

        $listener = new EmptySTT();
        $this->dispatcher->addListener('workflow.transition', $listener);
    }

    public function testInvoke(): void
    {
        $this->sm->apply($this->ord, 'save');
        self::assertEquals('saved', $this->ord->getState());
    }

    public function testInvokeSave(): void
    {
        $stt = new EmptySTT();
        $this->ord->setState('saved');
        $oldState = $this->ord->getState();

        $stt->save($this->ord, []);

        self::assertEquals($oldState, $this->ord->getState());
    }
}
