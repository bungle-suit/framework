<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Bungle\Framework\StateMachine\Events\SaveEvent;
use Bungle\Framework\StateMachine\HaveSaveActionResolveEvent;
use Bungle\Framework\StateMachine\MarkingStore\StatefulInterfaceMarkingStore;
use Bungle\Framework\StateMachine\SyncToDBInterface;
use Bungle\Framework\StateMachine\Vina;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use Bungle\Framework\Tests\StateMachine\EventListener\FakeAuthorizationChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SplObjectStorage;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\Workflow\Exception\TransitionException;
use Symfony\Component\Workflow\Metadata\InMemoryMetadataStore;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;
use Symfony\Component\Workflow\Transition;

final class VinaTest extends TestCase
{
    // Return [Vina, RequestStack, EventDispatcher]
    private function createVina(bool $mockDB = false): array
    {
        $reqStack = new RequestStack();
        $dispatcher = new EventDispatcher();
        $syncToDb = $mockDB ? $this->createMock(SyncToDBInterface::class): null;
        $vina = new Vina(
            self::createRegistry($dispatcher),
            new FakeAuthorizationChecker(
                'ROLE_ord_save',
                'ROLE_ord_print',
                'Role_prd_new'
            ),
            $reqStack,
            $dispatcher,
            $syncToDb
        );

        return [$vina, $reqStack, $dispatcher, $syncToDb];
    }

    public function testGetStateTitle(): void
    {
        /** @var Vina $vina */
        list($vina) = $this::createVina();
        self::assertEquals('未保存', $vina->getCurrentStateTitle(new Order()));
        $ord = new Order();
        $ord->setState('blah');
        self::assertEquals('blah', $vina->getCurrentStateTitle($ord));
    }

    public function testGetTransitionTitles(): void
    {
        list($vina) = $this::createVina();
        self::assertEquals([
            'save' => '保存',
            'update' => '保存',
            'print' => '打印',
            'check' => 'check',
        ], $vina->getTransitionTitles(new Order()));
    }

    public function testGetStateTitles(): void
    {
        list($vina) = $this::createVina();
        self::assertEquals([
            StatefulInterface::INITIAL_STATE => '未保存',
            'saved' => '已保存',
            'checked' => '已审核',
            'unchecked' => 'unchecked',
        ], $vina->getStateTitles(new Order()));
    }

    public function testGetPossibleTransitions(): void
    {
        list($vina) = $this::createVina();
        self::assertEquals(
            ['save'],
            $vina->getPossibleTransitions(new Order())
        );
    }

    public function testApplyTransitionSetAttrs(): void
    {
        $attrs = ['foo' => 1, 'bar'=>2];
        $ord = new Order();
        /** @var EventDispatcher $dispatcher */
        list($vina, , $dispatcher) = $this->createVina();
        $hit = 0;
        $dispatcher->addListener('workflow.ord.transition', function (TransitionEvent $e) use (&$hit, $attrs) {
            $hit ++;
            self::assertEquals($attrs, $e->getContext());
        });
        $vina->applyTransition($ord, 'save', $attrs);
        self::assertEquals(1, $hit);

    }

    public function testApplyTransitionSyncToDB(): void
    {
        /** @var SyncToDBInterface|MockObject $syncToDB */
        $ord = new Order();
        list($vina, , , $syncToDB) = $this->createVina(true);

        $syncToDB->expects($this->once())->method('syncToDB')->with($ord);
        $vina->applyTransition($ord, 'save');
    }

    public function testApplyTransitionFailed(): array
    {
        /** @var SyncToDBInterface|MockObject $syncToDB */
        /** @var Vina $vina */
        $ord = new Order();
        list($vina, $reqStack, , $syncToDB) = $this->createVina(true);

        $sess = new Session(new MockArraySessionStorage());

        $req = Request::create('/foo');
        $req->setSession($sess);
        $reqStack->push($req);
        $syncToDB->expects($this->never())->method('syncToDB');

        $vina->applyTransition($ord, 'check');
        self::assertNotEmpty($sess->getFlashBag()->get(Vina::FLASH_ERROR_MESSAGE));

        return [$vina, $sess];
    }

    /**
     * @depends testApplyTransitionFailed
     */
    public function testApplyTransitionRawFailed(array $args): void
    {
        /** @var Vina $vina */
        $this->expectException(TransitionException::class);
        list($vina) = $args;

        $vina->applyTransitionRaw(new Order(), 'check');
    }

    public function testGetTransitionRole(): void
    {
        $getRole = Vina::class.'::getTransitionRole';
        self::assertEquals('ROLE_ord_save', $getRole('ord', 'save'));
    }

    public function testSave(): void
    {
        /** @var SyncToDBInterface|MockObject $syncToDB */
        /** @var Vina $vina */
        /** @var EventDispatcher $dispatcher */
        list($vina, , $dispatcher, $syncToDB) = $this->createVina(true);

        $log = [];
        $ord = new Order();
        $attrs = ['foo' => 'bar'];
        $listener = function (SaveEvent $e) use (&$log, $ord, $attrs) {
            $log[] = ['hit', $e->getSubject()];
            self::assertSame($ord, $e->getSubject());
            self::assertEquals($attrs, $e->getAttrs());
        };
        $dispatcher->addListener( 'vina.ord.save', $listener );
        $syncToDB->expects($this->once())->method('syncToDB')->with($ord);

        $vina->save($ord, $attrs);
        self::assertEquals([['hit', $ord]], $log);
    }

    public function testHaveSaveAction(): void
    {
        list($vina, , $dispatcher) = $this->createVina();
        $ord = new Order();
        self::assertFalse($vina->haveSaveAction($ord));

        $dispatcher->addListener(
            'vina.ord.have_save_action',
            fn (HaveSaveActionResolveEvent $e) => $e->setHaveSaveAction()
        );
        self::assertTrue($vina->haveSaveAction($ord));
    }

    public function testCanSave(): void
    {
        /** @var Vina $vina */
        list($vina, , $dispatcher) = $this->createVina();
        $ord = new Order();
        $dispatcher->addListener(
            'vina.ord.have_save_action',
            fn (HaveSaveActionResolveEvent $e) => $e->setHaveSaveAction()
        );
        self::assertTrue($vina->canSave($ord));

        $ord->setState('checked');
        self::assertFalse($vina->canSave($ord));
    }

    private static function createOrderWorkflow(EventDispatcher $dispatcher): StateMachine
    {
        $trans = [
          $save = new Transition('save', StatefulInterface::INITIAL_STATE, 'saved'),
          $update = new Transition('update', 'saved', 'saved'),
          $print = new Transition('print', 'saved', 'saved'),
          new Transition('check', 'saved', 'checked'),
        ];

        $transMeta = new SplObjectStorage();
        $transMeta[$save] = ['title' => '保存'];
        $transMeta[$update] = ['title' => '保存'];
        $transMeta[$print] = ['title' => '打印'];

        $definition = (new DefinitionBuilder())
          ->setMetadataStore(new InMemoryMetadataStore(
              [],
              [
                  'saved' => ['title' => '已保存'],
                  'checked' => ['title' => '已审核'],
              ],
              $transMeta,
          ))
          ->addPlaces([StatefulInterface::INITIAL_STATE, 'saved', 'checked', 'unchecked'])
          ->addTransitions($trans)
          ->build();

        $marking = new StatefulInterfaceMarkingStore();

        return new StateMachine($definition, $marking, $dispatcher, 'ord');
    }

    private static function createRegistry(EventDispatcher $dispatcher): Registry
    {
        $r = new Registry();
        $r->addWorkflow(
            self::createOrderWorkflow($dispatcher),
            new InstanceOfSupportStrategy(Order::class),
        );

        return $r;
    }
}
