<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Entity;

use Bungle\Framework\Entity\ArrayEntityDiscovery;
use Bungle\Framework\Entity\ArrayHighResolver;
use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\Exceptions;
use PHPUnit\Framework\TestCase;

final class EntityRegistryTest extends TestCase
{

    public function testEntities(): void
    {
        $dis = new ArrayEntityDiscovery(
            $entities = [
                Order::class,
                OrderLine::class,
            ]
        );
        $reg = new EntityRegistry($dis, new ArrayHighResolver([]));
        self::assertEquals($entities, $reg->getEntities());
    }

    public function testGetHigh(): void
    {
        $dis = new ArrayEntityDiscovery(
            [
                $ord = Order::class,
                $ordLine = OrderLine::class,
            ]
        );
        $resolver = new ArrayHighResolver(
            [
                $ord => 'ord',
                $ordLine => 'oln',
            ]
        );
        $reg = new EntityRegistry($dis, $resolver);

        self::assertEquals('ord', $reg->getHigh($ord));
    }

    public function testNewEntity(): void
    {
        $dis = new ArrayEntityDiscovery(
            [
                Entities\Order::class,
            ]
        );
        $resolver = new ArrayHighResolver(
            [
                Entities\Order::class => 'ord',
            ]
        );
        $reg = new EntityRegistry($dis, $resolver);
        self::assertInstanceOf(
            Entities\Order::class,
            $reg->createEntity('ord')
        );
    }

    public function testGetHighBadEntityClass(): void
    {
        $order = Order::class;
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
        $order = Order::class;
        $this->expectExceptionObject(Exceptions::highNotDefinedOn($order));

        $dis = new ArrayEntityDiscovery([$order]);
        $reg = new EntityRegistry($dis, new ArrayHighResolver([]));
        $reg->getHigh($order);
    }

    public function testDupHigh(): void
    {
        $this->expectExceptionObject(
            Exceptions::highDuplicated(
                'ord',
                Order::class,
                OrderLine::class
            )
        );

        $dis = new ArrayEntityDiscovery(
            [
                Order::class,
                OrderLine::class,
            ]
        );
        $resolver = new ArrayHighResolver(
            [
                Order::class => 'ord',
                OrderLine::class => 'ord',
            ]
        );
        $reg = new EntityRegistry($dis, $resolver);
        $reg->getHigh(Order::class);
    }

    public function testGetEntityByHigh(): void
    {
        $dis = new ArrayEntityDiscovery(
            [
                Order::class,
                OrderLine::class,
            ]
        );
        $resolver = new ArrayHighResolver(
            [
                Order::class => 'ord',
                OrderLine::class => 'oln',
            ]
        );
        $reg = new EntityRegistry($dis, $resolver);
        self::assertEquals(Order::class, $reg->getEntityByHigh('ord'));
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
