<?php
/** @noinspection PhpParamsInspection */
declare(strict_types=1);

namespace Bungle\Framework\Tests\IDName;

use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\IDName\HighIDNameTranslatorChain;
use Bungle\Framework\IDName\HighIDNameTranslatorInterface;
use Bungle\Framework\Tests\Entity\Order;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class HighIDNameTranslatorChainTest extends MockeryTestCase
{
    public function testTranslate()
    {
        $entityRegistry = Mockery::mock(EntityRegistry::class);
        $entityRegistry->allows('getEntityByHigh')->with('ord')->andReturn(Order::class);
        $t1 = Mockery::mock(HighIDNameTranslatorInterface::class);
        $t2 = Mockery::mock(HighIDNameTranslatorInterface::class);
        $t1->expects('supports')->with('ord', Order::class, 123)->andReturn(true);
        $t1->expects('supports')->with('ord', Order::class, 456)->andReturn(false);
        $t2->expects('supports')->with('ord', Order::class, 456)->andReturn(true);
        $t1->expects('translate')->with('ord', Order::class, 123)->andReturn('foo');
        $t2->expects('translate')->with('ord', Order::class, 456)->andReturn('bar');

        $chain = new HighIDNameTranslatorChain($entityRegistry, [$t1, $t2]);
        self::assertEquals('foo', $chain->translate('ord', 123));
        self::assertEquals('bar', $chain->translate('ord', 456));
    }

    public function testNoSupportedTranslator(): void
    {
        $entityRegistry = Mockery::mock(EntityRegistry::class);
        $entityRegistry->allows('getEntityByHigh')->with('ord')->andReturn(Order::class);
        $t1 = Mockery::mock(HighIDNameTranslatorInterface::class);
        $t1->allows('supports')->andReturn(false);

        $chain = new HighIDNameTranslatorChain($entityRegistry, [$t1]);
        self::assertEquals('123', $chain->translate('ord', 123));
    }
}
