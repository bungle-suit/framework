<?php
/** @noinspection PhpParamsInspection */
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\Steps;

use Bungle\Framework\Ent\Code\CodeGenerator;
use Bungle\Framework\Entity\CommonTraits\CodeAble;
use Bungle\Framework\Entity\CommonTraits\CodeAbleInterface;
use Bungle\Framework\StateMachine\StepContext;
use Bungle\Framework\StateMachine\Steps\SetCodeStep;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\WorkflowInterface;

class SetCodeStepTest extends MockeryTestCase
{
    public function test(): void
    {
        $o = new class() implements CodeAbleInterface {
            use CodeAble;
        };
        $codeGen = Mockery::mock(CodeGenerator::class);
        $codeGen->expects('generate')->with($o)->andReturn('fooCode');

        $step = new SetCodeStep($codeGen);
        $step($o, new StepContext(
            $this->createMock(WorkflowInterface::class),
            $this->createMock(Transition::class),
            []
        ));
        self::assertEquals('fooCode', $o->getCode());
    }
}
