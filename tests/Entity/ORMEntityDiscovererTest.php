<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Entity;

use Bungle\Framework\Entity\ORMEntityDiscoverer;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use Bungle\Framework\Tests\StateMachine\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

class ORMEntityDiscovererTest extends TestCase
{
    private function createManagerRegistry(): ManagerRegistry
    {
        $factory = $this->createStub(ClassMetadataFactory::class);
        $manager = $this->createStub(ObjectManager::class);
        $manager->method('getMetadataFactory')->willReturn($factory);
        $registry = $this->createStub(ManagerRegistry::class);
        $registry->method('getManager')->willReturn($manager);
        $factory->method('getAllMetadata')->willReturn([
        ($meta1 = $this->createStub(ClassMetadata::class)),
        ($meta2 = $this->createStub(ClassMetadata::class)),
        ($meta3 = $this->createStub(ClassMetadata::class)),
        ]);
        $meta1->method('getName')->willReturn(Order::class);
        $meta2->method('getName')->willReturn(Product::class);
        $meta3->method('getName')->willReturn(self::class);

        return $registry;
    }

    public function testFoo(): void
    {
        $registry = $this->createManagerRegistry();
        $dis = new ORMEntityDiscoverer($registry);
        self::assertEquals([
        Order::class, Product::class,
        ], iterator_to_array($dis->getAllEntities()));
    }
}
