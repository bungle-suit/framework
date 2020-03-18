<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Entity;

use Bungle\Framework\Entity\ArrayEntityMetaResolver;
use Bungle\Framework\Entity\EntityMeta;
use Bungle\Framework\Entity\EntityMetaRepository;
use PHPUnit\Framework\TestCase;

final class EntityMetaRepositoryTest extends TestCase
{
    public function testGetMeta(): void
    {
        $rep = new EntityMetaRepository(
            new ArrayEntityMetaResolver([
              self::class => ($meta = new EntityMeta(self::class, 'foo', [])),
            ]),
        );

        self::assertSame($meta, $rep->get(self::class));
        self::assertSame($meta, $rep->get(self::class)); // does cache works
    }
}
