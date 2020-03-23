<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Bungle\Framework\StateMachine\HaveSaveActionResolveEvent;
use Bungle\Framework\StateMachine\MarkingStore\StatefulInterfaceMarkingStore;
use Bungle\Framework\StateMachine\Vina;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use Bungle\Framework\Tests\StateMachine\EventListener\FakeAuthorizationChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Exception\TransitionException;
use Symfony\Component\Workflow\Metadata\InMemoryMetadataStore;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;
use Symfony\Component\Workflow\Transition;

final class VinaTest extends TestCase
{
    // Return [Vina, RequestStack, EventDispatcher]
    private function createVina(): array
    {
        $reqStack = new RequestStack();
        $dispatcher = new EventDispatcher();
        $vina = new Vina(
            self::createRegistry(),
            new FakeAuthorizationChecker(
                'ROLE_ord_save',
                'ROLE_ord_print',
                'Role_prd_new'
            ),
            $reqStack,
            $dispatcher
        );

        return [$vina, $reqStack, $dispatcher];
    }

    public function testGetStateTitle(): void
    {
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

    public function testApplyTransitionFailed(): array
    {
        $ord = new Order();
        list($vina, $reqStack) = $this->createVina();

        $sess = new Session(new MockArraySessionStorage());

        $req = Request::create('/foo');
        $req->setSession($sess);
        $reqStack->push($req);

        $vina->applyTransition($ord, 'check');
        self::assertNotEmpty($sess->getFlashBag()->get(Vina::FLASH_ERROR_MESSAGE));

        return [$vina, $sess];
    }

    /**
     * @depends testApplyTransitionFailed
     */
    public function testApplyTransitionRawFailed(array $args): void
    {
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
        list($vina, , $dispatcher) = $this->createVina();

        $log = [];
        $ord = new Order();
        $listener = function ($e) use (&$log) {
            $log[] = ['hit', $e->getSubject()];
        };
        $dispatcher->addListener(
            'vina.ord.save',
            $listener
        );
        $vina->save($ord);
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

    private static function createOrderWorkflow(): StateMachine
    {
        $trans = [
          $save = new Transition('save', StatefulInterface::INITIAL_STATE, 'saved'),
          $update = new Transition('update', 'saved', 'saved'),
          $print = new Transition('print', 'saved', 'saved'),
          new Transition('check', 'saved', 'checked'),
        ];

        $transMeta = new \SplObjectStorage();
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

        return new StateMachine($definition, $marking, null, 'ord');
    }

    private static function createRegistry(): Registry
    {
        $r = new Registry();
        $r->addWorkflow(
            self::createOrderWorkflow(),
            new InstanceOfSupportStrategy(Order::class),
        );

        return $r;
    }
}
