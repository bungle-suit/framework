<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Entity;

use Bungle\Framework\Entity\ArrayEntityDiscovery;
use Bungle\Framework\Entity\ArrayHighResolver;
use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\Exception\Exceptions;
use PHPUnit\Framework\TestCase;

final class EntityRegistryTest extends TestCase
{
    const ORDER = 'order\\order';
    const ORDER_LINE = 'order\\orderLine';

    public function testEntites(): void
    {
        $dis = new ArrayEntityDiscovery(
            $entites = [
              self::ORDER,
              self::ORDER_LINE,
            ]
        );
        $reg = new EntityRegistry($dis, new ArrayHighResolver([]));
        self::assertEquals($entites, $reg->entities);
    }

    public function testGetHigh(): void
    {
        $dis = new ArrayEntityDiscovery([
          $ord = self::ORDER,
          $ordLine = self::ORDER_LINE,
        ]);
        $resolver = new ArrayHighResolver([
          $ord => 'ord',
          $ordLine => 'oln',
        ]);
        $reg = new EntityRegistry($dis, $resolver);

        self::assertEquals('ord', $reg->getHigh($ord));
    }

    public function testNewEntity(): void
    {
        $dis = new ArrayEntityDiscovery([
          Entities\Order::class,
        ]);
        $resolver = new ArrayHighResolver([
          Entities\Order::class => 'ord',
        ]);
        $reg = new EntityRegistry($dis, $resolver);
        self::assertInstanceOf(
            Entities\Order::class,
            $reg->createEntity('ord')
        );
    }

    public function testGetHighBadEntityClass(): void
    {
        $order = self::ORDER;
        $this->expectExceptionObject(Exceptions::entityNotDefined($order));
        $reg = new EntityRegistry(
            new ArrayEntityDiscovery([]),
            new ArrayHighResolver([])
        );
        $reg->getHigh($order);
    }

    public function testGetHighNotExistEntityClass(): void
    {
        $this->expectExceptionObject(Exceptions::entityNotDefined(self::class));
        $reg = new EntityRegistry(
            new ArrayEntityDiscovery([]),
            new ArrayHighResolver([])
        );
        $reg->getHigh(self::class);
    }

    public function testGetHighNoHighDefined(): void
    {
        $order = self::ORDER;
        $this->expectExceptionObject(Exceptions::highNotDefinedOn($order));

        $dis = new ArrayEntityDiscovery([$order]);
        $reg = new EntityRegistry($dis, new ArrayHighResolver([]));
        $reg->getHigh($order);
    }

    public function testDupHigh(): void
    {
        $this->expectExceptionObject(Exceptions::highDuplicated('ord', self::ORDER, self::ORDER_LINE));

        $dis = new ArrayEntityDiscovery([
          self::ORDER,
          self::ORDER_LINE,
        ]);
        $resolver = new ArrayHighResolver([
          self::ORDER => 'ord',
          self::ORDER_LINE => 'ord',
        ]);
        $reg = new EntityRegistry($dis, $resolver);
        $reg->getHigh(self::ORDER);
    }

    public function testGetEntityByHigh(): void
    {
        $dis = new ArrayEntityDiscovery([
          self::ORDER,
          self::ORDER_LINE,
        ]);
        $resolver = new ArrayHighResolver([
          self::ORDER => 'ord',
          self::ORDER_LINE => 'oln',
        ]);
        $reg = new EntityRegistry($dis, $resolver);
        self::assertEquals(self::ORDER, $reg->getEntityByHigh('ord'));
    }

    public function testGetEntityByHighNotFound(): void
    {
        $this->expectExceptionObject(Exceptions::highNotFound('ord'));

        $dis = new ArrayEntityDiscovery([]);
        $resolver = new ArrayHighResolver([]);
        $reg = new EntityRegistry($dis, $resolver);
        $reg->getEntityByHigh('ord');
    }
}
