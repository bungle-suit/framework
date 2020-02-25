<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Bungle\Framework\StateMachine\MarkingStore\StatefulInterfaceMarkingStore;
use Bungle\Framework\StateMachine\Vina;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use Bungle\Framework\Tests\StateMachine\EventListener\FakeAuthorizationChecker;
use Doctrine\ODM\MongoDB\DocumentManager;
use PHPUnit\Framework\TestCase;
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
    // Return [Vina, DocumentManager, RequestStack]
    private function createVina(): array
    {
        $docManager = $this->createMock(DocumentManager::class);
        $reqStack = new RequestStack();
        $vina = new Vina(
            self::createRegistry(),
            new FakeAuthorizationChecker(
                'ROLE_ord_save',
                'ROLE_ord_print',
                'Role_prd_new'
            ),
            $docManager,
            $reqStack,
        );

        return [$vina, $docManager, $reqStack];
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
            ['save', 'print'],
            $vina->getPossibleTransitions(new Order())
        );
    }

    public function testApplyTransitionSucceed(): void
    {
        $ord = new Order();
        list($vina, $docManager) = $this->createVina();
        $docManager->expects($this->once())->method('persist')->with($ord);
        $docManager->expects($this->once())->method('flush');

        $vina->applyTransition($ord, 'save');
    }

    public function testApplyTransitionFailed(): array
    {
        $ord = new Order();
        list($vina, $docManager, $reqStack) = $this->createVina();
        $docManager->expects($this->never())->method('persist')->with($ord);
        $docManager->expects($this->never())->method('flush');

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

    public function testApplyTransitionRawSucceed(): void
    {
        $ord = new Order();
        list($vina, $docManager) = $this->createVina();
        $docManager->expects($this->once())->method('persist')->with($ord);
        $docManager->expects($this->once())->method('flush');

        $vina->applyTransitionRaw($ord, 'save');
    }

    public function testGetTransitionRole(): void
    {
        $getRole = Vina::class.'::getTransitionRole';
        self::assertEquals('ROLE_ord_save', $getRole('ord', 'save'));
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
