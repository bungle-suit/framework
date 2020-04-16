<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Entity;

use Bungle\Framework\Entity\EntityMeta;
use Bungle\Framework\Entity\EntityPropertyMeta;
use Bungle\Framework\Exceptions;
use PHPUnit\Framework\TestCase;

final class EntityMetaTest extends TestCase
{
    public function testName(): void
    {
        $meta = new EntityMeta(self::class, 'foobar', []);
        self::assertEquals('EntityMetaTest', $meta->name());
    }

    public function testGetProperty(): void
    {
        $meta = new EntityMeta(self::class, 'foobar', [
            $p1 = new EntityPropertyMeta('id', 'ID'),
            $p2 = new EntityPropertyMeta('name', 'Name'),
        ]);

        self::assertSame($p1, $meta->getProperty('id'));
        self::assertSame($p2, $meta->getProperty('name'));
    }

    public function testGetPropertyNotExist(): void
    {
        $this->expectExceptionObject(Exceptions::propertyNotFound(self::class, 'id'));

        $meta = new EntityMeta(self::class, 'foobar', []);
        $meta->getProperty('id');
    }
}
