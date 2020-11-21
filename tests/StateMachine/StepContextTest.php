<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine;

use Bungle\Framework\StateMachine\StepContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\WorkflowInterface;

class StepContextTest extends TestCase
{
    public function testInitialAttrs(): void
    {
        $attrs = [
            'attr1'=> 'foo',
            'attr2' => 'bar',
        ];
        $ctx = new StepContext(
            $this->createStub(WorkflowInterface::class),
            $this->createStub(Transition::class),
            $attrs
        );

        self::assertEquals($attrs, $ctx->all());

    }
}
