<?php
/** @noinspection PhpParamsInspection */

declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Bungle\Framework\StateMachine\MarkingStore\StatefulInterfaceMarkingStore;
use Bungle\Framework\StateMachine\STT\AbstractSTT;
use Bungle\Framework\StateMachine\STTLocator\STTLocatorInterface;
use Bungle\Framework\StateMachine\SyncToDBInterface;
use Bungle\Framework\StateMachine\Vina;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use Bungle\Framework\Tests\StateMachine\EventListener\FakeAuthorizationChecker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
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

final class VinaTest extends MockeryTestCase
{
    /**
     * @return array{Vina, RequestStack, EventDispatcher, (SyncToDBInterface&MockInterface)|null, STTLocatorInterface|MockInterface}
     */
    private function createVina(bool $mockDB = false): array
    {
        $reqStack = new RequestStack();
        $dispatcher = new EventDispatcher();
        $syncToDb = $mockDB ? Mockery::mock(SyncToDBInterface::class) : null;
        $sttLocator = Mockery::mock(STTLocatorInterface::class);
        $vina = new Vina(
            self::createRegistry($dispatcher),
            new FakeAuthorizationChecker(
                'ROLE_ord_save',
                'ROLE_ord_print',
                'Role_prd_new'
            ),
            $reqStack,
            $sttLocator,
            $syncToDb
        );

        return [$vina, $reqStack, $dispatcher, $syncToDb, $sttLocator];
    }

    public function testGetStateTitle(): void
    {
        [$vina] = $this::createVina();
        self::assertEquals('未保存', $vina->getCurrentStateTitle(new Order()));
        $ord = new Order();
        $ord->setState('blah');
        self::assertEquals('blah', $vina->getCurrentStateTitle($ord));
    }

    public function testGetTransitionTitles(): void
    {
        [$vina] = $this::createVina();
        self::assertEquals(
            [
                'save' => '保存',
                'update' => '保存',
                'print' => '打印',
                'check' => 'check',
            ],
            $vina->getTransitionTitles(new Order())
        );
    }

    public function testGetStateTitles(): void
    {
        [$vina] = $this::createVina();
        self::assertEquals(
            [
                StatefulInterface::INITIAL_STATE => '未保存',
                'saved' => '已保存',
                'checked' => '已审核',
                'unchecked' => 'unchecked',
            ],
            $vina->getStateTitles(new Order())
        );
    }

    public function testGetPossibleTransitions(): void
    {
        [$vina] = $this::createVina();
        self::assertEquals(
            ['save'],
            $vina->getPossibleTransitions(new Order())
        );
    }

    public function testApplyTransitionSetAttrs(): void
    {
        $attrs = ['foo' => 1, 'bar' => 2];
        $ord = new Order();
        [$vina, , $dispatcher] = $this->createVina();
        $hit = 0;
        $dispatcher->addListener(
            'workflow.ord.transition',
            function (TransitionEvent $e) use (&$hit, $attrs) {
                $hit++;
                self::assertEquals($attrs, $e->getContext());
            }
        );
        $vina->applyTransition($ord, 'save', $attrs);
        self::assertEquals(1, $hit);
    }

    public function testApplyTransitionSyncToDB(): void
    {
        $ord = new Order();
        [$vina, , , $syncToDB] = $this->createVina(true);

        assert($syncToDB !== null);
        $syncToDB->expects('syncToDB')->with($ord);
        $vina->applyTransition($ord, 'save');
    }

    /**
     * @return array{Vina, Session}
     */
    public function testApplyTransitionFailed(): array
    {
        $ord = new Order();
        [$vina, $reqStack, , $syncToDB] = $this->createVina(true);
        assert($syncToDB !== null);

        $sess = new Session(new MockArraySessionStorage());

        $req = Request::create('/foo');
        $req->setSession($sess);
        $reqStack->push($req);
        $syncToDB->expects('syncToDB')->never();

        $vina->applyTransition($ord, 'check');
        self::assertNotEmpty($sess->getFlashBag()->get(Vina::FLASH_ERROR_MESSAGE));

        return [$vina, $sess];
    }

    public function testApplyTransitionFailedNoCurrentRequest(): void
    {
        $this->expectException(TransitionException::class);

        $ord = new Order();
        [$vina, , , $syncToDB] = $this->createVina(true);
        assert($syncToDB !== null);

        $syncToDB->expects('syncToDB')->never();

        $vina->applyTransition($ord, 'check');
    }

    /**
     * @depends testApplyTransitionFailed
     * @param array{Vina, Session} $args
     */
    public function testApplyTransitionRawFailed(array $args): void
    {
        $this->expectException(TransitionException::class);
        [$vina] = $args;

        $vina->applyTransitionRaw(new Order(), 'check');
    }

    public function testGetTransitionRole(): void
    {
        $getRole = Vina::class.'::getTransitionRole';
        self::assertEquals('ROLE_ord_save', $getRole('ord', 'save'));
    }

    public function testSave(): void
    {
        [$vina, , , $syncToDB, $sttLocator] = $this->createVina(true);
        assert($syncToDB !== null);
        $ord = new Order();
        $attrs = ['foo' => 'bar'];

        /** @var AbstractSTT<Order>|StatefulInterface|MockInterface $stt */
        $stt = Mockery::mock(AbstractSTT::class, StatefulInterface::class);
        $sttLocator->expects('getSTTForClass')
                   ->with(Order::class)
                   ->andReturn($stt);
        $stt->expects('save')
            ->with($ord, $attrs);
        $syncToDB->expects('syncToDB')->with($ord);

        $vina->save($ord, $attrs);
    }

    public function testHaveSaveAction(): void
    {
        [$vina, , , , $sttLocator] = $this->createVina();
        $ord = new Order();

        /** @var AbstractSTT<Order>&StatefulInterface&MockInterface $stt */
        $stt = Mockery::mock(AbstractSTT::class, StatefulInterface::class);
        $sttLocator->expects('getSTTForClass')
                   ->with(Order::class)->andReturn($stt);
        $stt->expects('canSave')->with($ord)->andReturn(false);

        self::assertFalse($vina->haveSaveAction($ord));
    }

    public function testCanSave(): void
    {
        [$vina, , , , $sttLocator] = $this->createVina();
        $ord = new Order();

        $stt = Mockery::mock(AbstractSTT::class, StatefulInterface::class);
        $sttLocator->expects('getSTTForClass')->twice()
                   ->with(Order::class)->andReturn($stt);
        $stt->expects('canSave')->twice()->with($ord)->andReturn(true);

        self::assertTrue($vina->canSave($ord));

        $ord->setState('checked');
        self::assertFalse($vina->canSave($ord));
    }

    public function testCreateNew(): void
    {
        [$vina, , , , $sttLocator] = $this->createVina();

        $stt = Mockery::mock(AbstractSTT::class, StatefulInterface::class);
        $sttLocator->expects('getSTTForClass')
                   ->with(Order::class)->andReturn($stt);
        $ord = new Order();
        $stt->expects('createNew')->with()->andReturn($ord);
        self::assertSame($ord, $vina->createNew(Order::class));
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
            ->setMetadataStore(
                new InMemoryMetadataStore(
                    [],
                    [
                        'saved' => ['title' => '已保存'],
                        'checked' => ['title' => '已审核'],
                    ],
                    $transMeta,
                )
            )
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
