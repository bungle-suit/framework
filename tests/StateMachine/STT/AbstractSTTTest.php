<?php /** @noinspection PhpParamsInspection */

declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\STT;

use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\Exceptions;
use Bungle\Framework\StateMachine\STT\AbstractSTT;
use Bungle\Framework\StateMachine\STT\InitEntityInterface;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use Bungle\Framework\Tests\StateMachine\EventListener\TestBase;
use Mockery;
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

    public function testDisableAbortByAttr(): void
    {
        $this->ord->setState('saved');
        $this->sm->apply($this->ord, 'check', ['abort' => false]);
        self::assertTrue(true, 'Ensure no exception raised');
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

    public function testSave(): void
    {
        $stt = new OrderSTT();
        $this->ord->setState('saved');
        $oldState = $this->ord->getState();

        $stt->save($this->ord, ['attr' => ' foo']);
        self::assertEquals('before save foo;save;after save', $this->ord->log);
        self::assertEquals('bar', $this->ord->before);
        self::assertEquals('foo', $this->ord->name);
        self::assertEquals('after', $this->ord->after);

        // invokeSave() prevent steps to manipulate state.
        self::assertEquals($oldState, $this->ord->getState());
    }

    public function testSaveNotConfigured(): void
    {
        $old = set_error_handler(fn () => true, E_USER_NOTICE);
        try {
            $stt = new OrderSTT();
            $this->ord->setState('checked');

            $stt->save($this->ord, []);
            self::assertNull($this->ord->log ?? null);
            self::assertNull($this->ord->before ?? null);
            self::assertNull($this->ord->name ?? null);
            self::assertNull($this->ord->after ?? null);

            // invokeSave() prevent steps to manipulate state.
            self::assertEquals('checked', $this->ord->getState());
        } finally {
            set_error_handler($old, E_USER_NOTICE);
        }
    }

    public function testSaveEmptyConfigured(): void
    {
        $stt = new OrderSTT();
        $stt->save($this->ord, []);
        self::assertEquals('before save;after save', $this->ord->log);
    }

    public function testCanSave(): void
    {
        $stt = new OrderSTT();
        // configured empty
        self::assertTrue($stt->canSave($this->ord));

        // configured
        $this->ord->setState('saved');
        self::assertTrue($stt->canSave($this->ord));

        // Not configured
        $this->ord->setState('checked');
        self::assertFalse($stt->canSave($this->ord));
    }

    public function testCreateNewNoInitEntityInterface(): void
    {
        $registry = Mockery::mock(EntityRegistry::class);
        $registry->allows('getEntityByHigh')->with('ord')->andReturn(Order::class);
        $stt = new OrderSTT();
        $stt->setEntityRegistry($registry);
        self::assertEquals(new Order(), $stt->createNew());
    }

    public function testCreateNewWithInitEntityInterface(): void
    {
        $registry = Mockery::mock(EntityRegistry::class);
        $registry->allows('getEntityByHigh')->with('ord')->andReturn(Order::class);
        $stt = new Class extends AbstractSTT implements InitEntityInterface {
            protected static function getHigh(): string {
                return 'ord';
            }

            public static function initCode(Order $ord): void
            {
                $ord->code = 'xx1234xx';
            }

            public static function setState(Order $ord): void
            {
                $ord->setState('closed');
            }

            public function initSteps(): array {
                return [
                    [self::class, 'initCode'],
                    [self::class, 'setState'],
                ];
            }

            protected function steps(): array {
                return ['actions' => []];
            }
        };
        $stt->setEntityRegistry($registry);

        $exp = new Order();
        $exp->code = 'xx1234xx';
        $exp->setState('closed');
        self::assertEquals($exp, $stt->createNew());
    }
}
