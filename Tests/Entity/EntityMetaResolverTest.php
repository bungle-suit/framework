<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Entity;

use Bungle\Framework\Annotation\LogicName;
use Bungle\Framework\Entity\EntityMeta;
use Bungle\Framework\Entity\EntityMetaResolver;
use Bungle\Framework\Entity\EntityPropertyMeta;
use PHPUnit\Framework\TestCase;

/**
 * @LogicName("订单")
 */
class Order
{
    /**
     * @LogicName("编号")
     */
    public int $id;

    public string $name;
}

final class EntityMetaResolverTest extends TestCase // phpcs:ignore
{
    public function test(): void
    {
        $resolver = new EntityMetaResolver();
        $meta = $resolver->resolveEntityMeta(Order::class);
        self::assertEquals(new EntityMeta(
            Order::class,
            '订单',
            [
              new EntityPropertyMeta('id', '编号'),
              new EntityPropertyMeta('name', 'name'),
            ],
        ), $meta);
    }
}
