<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\MarkingStore;

use PHPUnit\Framework\TestCase;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Bungle\Framework\StateMachine\MarkingStore\StatefulInterfaceMarkingStore;
use Symfony\Component\Workflow\Marking;

final class StatefulInterfaceMarkingStoreTest extends TestCase
{
    public function testGet(): void
    {
        $store = new StatefulInterfaceMarkingStore();
        $ord = new Order();

        $marking = $store->getMarking($ord);
        self::assertEquals([StatefulInterface::INITIAL_STATE=> 1], $marking->getPlaces());
    }

    public function testSet(): void
    {
        $store = new StatefulInterfaceMarkingStore();
        $ord = new Order();

        $store->setMarking($ord, new Marking(['saved' => 1]));
        self::assertEquals('saved', $ord->getState());
    }
}
