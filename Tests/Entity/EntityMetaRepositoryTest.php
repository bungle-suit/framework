<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Entity;

use Bungle\Framework\Entity\ArrayEntityDiscovery;
use Bungle\Framework\Entity\ArrayEntityMetaResolver;
use Bungle\Framework\Entity\ArrayHighResolver;
use Bungle\Framework\Entity\EntityMeta;
use Bungle\Framework\Entity\EntityMetaRepository;
use Bungle\Framework\Entity\EntityRegistry;
use PHPUnit\Framework\TestCase;

final class EntityMetaRepositoryTest extends TestCase
{
    public function testGetMeta(): void
    {
        $reg = new EntityRegistry(
            new ArrayEntityDiscovery([self::class]),
            new ArrayHighResolver([self::class => 'ord'])
        );
        $rep = new EntityMetaRepository(
            $reg,
            new ArrayEntityMetaResolver([
            self::class => ($meta = new EntityMeta(self::class, 'foo', [])),
            ]),
        );

        self::assertSame($meta, $rep->get('ord'));
        self::assertSame($meta, $rep->get('ord')); // does cache works
    }
}
