<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\MarkingStore;

use Bungle\Framework\StateMachine\MarkingStore\PropertyMarkingStore;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Workflow\Marking;

final class PropertyMarkingStoreTest extends TestCase
{
    private PropertyMarkingStore $store;
    private \StdClass $obj;

    public function setUp(): void
    {
        $this->store = new PropertyMarkingStore('state');
        $this->obj = new \StdClass();
    }

    public function testGetMarking(): void
    {
        $this->obj->state = 'foo';
        $marking = $this->store->getMarking($this->obj);
        self::assertEquals(['foo' => 1], $marking->getPlaces());
    }

    public function testGetMarkingWrongProperty(): void
    {
        self::expectException(\AssertionError::class);
        $this->obj->state = null;
        $this->store->getMarking($this->obj);
    }

    public function testSetMarking(): void
    {
        $this->store->setMarking($this->obj, new Marking(['saved' => 1]));
        self::assertEquals('saved', $this->obj->state);
    }
}
