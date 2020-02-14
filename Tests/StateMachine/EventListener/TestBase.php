<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\EventListener;

use Bungle\Framework\StateMachine\Entity;
use Symfony\Component\Workflow\Transition;
use Bungle\Framework\StateMachine\MarkingStore\PropertyMarkingStore;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\StateMachine;

abstract class TestBase extends TestCase
{
    protected StateMachine $sm;
    protected EventDispatcher $dispatcher;
    protected Order $ord;

    public function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
        $this->sm = self::createOrderWorkflow($this->dispatcher);
        $this->ord = new Order();
    }

    private static function createOrderWorkflow(EventDispatcher $dispatcher): StateMachine
    {
        $definitionBuilder = new DefinitionBuilder();
        $definition = $definitionBuilder->addPlaces([
        Entity::INITIAL_STATE, 'saved', 'checked'])
          ->addTransition(new Transition('save', Entity::INITIAL_STATE, 'saved'))
          ->addTransition(new Transition('update', 'saved', 'saved'))
          ->addTransition(new Transition('print', 'saved', 'saved'))
          ->addTransition(new Transition('check', 'saved', 'checked'))
          ->build();

        $marking = new PropertyMarkingStore('state');
        return new StateMachine($definition, $marking, $dispatcher, 'ord');
    }
}
