<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Entity;

use ArrayIterator;
use Bungle\Framework\Entity\CombineEntityDiscover;
use Bungle\Framework\Entity\EntityDiscovererInterface;
use PHPUnit\Framework\TestCase;

final class CombineEntityDiscoverTest extends TestCase
{
    public function test(): void
    {
        $c1 = $this->createMock(EntityDiscovererInterface::class);
        $c2 = $this->createMock(EntityDiscovererInterface::class);
        $c1->method('getAllEntities')->willReturn(new ArrayIterator([
        'app\classA', 'app\classB',
        ]));
        $c2->method('getAllEntities')->willReturn(new ArrayIterator([
        'app\classC', 'app\classD',
        ]));
        $combined = new CombineEntityDiscover([$c1, $c2]);
        self::assertEquals([
        'app\classA', 'app\classB',
        'app\classC', 'app\classD',
        ], iterator_to_array($combined->getAllEntities()));
    }
}
