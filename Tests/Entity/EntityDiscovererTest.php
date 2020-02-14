<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Bungle\Framework\Entity\EntityDiscoverer;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use Bungle\Framework\Tests\StateMachine\Entity\Product;

final class EntityDiscovererTest extends TestCase
{
    public function test(): void
    {
        $managerRegistry = $this->createManagerRegistry();
        $dis = new EntityDiscoverer($managerRegistry);
        self::assertEquals([
        Order::class, Product::class,
        ], iterator_to_array($dis->getAllEntities()));
    }

    public function createManagerRegistry(): ManagerRegistry
    {
        $mappingDriver = $this->createStub(MappingDriver::class);
        $mappingDriver->method('getAllClassNames')->willReturn([
          Order::class,
         self::class,
          Product::class,
        ]);
        $config = $this->createStub(Configuration::class);
        $config->method('getMetadataDriverImpl')->willReturn($mappingDriver);
        $defManager = $this->createStub(DocumentManager::class);
        $defManager->method('getConfiguration')->willReturn($config);

        $r = $this->createStub(ManagerRegistry::class);
        $r->method('getManager')
          ->willReturn($defManager);
        return $r;
    }
}
