<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Bungle\Framework\Entity\EntityMetaResolver;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Bungle\Framework\Annotation\LogicName;
use Bungle\Framework\Entity\EntityMeta;
use Bungle\Framework\Entity\EntityPropertyMeta;

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
        $docManager = $this->createStub(DocumentManager::class);
        $clsMeta = $this->createStub(ClassMetadata::class);
        $clsMeta->method('getTypeOfField')->willReturnMap([
        ['id', 'int'],
        ['name', 'string'],
        ]);
        $clsMeta->method('getFieldNames')->willReturn(['id', 'name']);
        $docManager->method('getClassMetadata')->willReturn($clsMeta);

        $resolver = new EntityMetaResolver($docManager);
        $meta = $resolver->resolveEntityMeta(Order::class);
        self::assertEquals(new EntityMeta(
            Order::class,
            '订单',
            [
            new EntityPropertyMeta('id', '编号', 'int'),
            new EntityPropertyMeta('name', 'name', 'string'),
            ],
        ), $meta);
    }
}
