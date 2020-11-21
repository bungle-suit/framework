<?php

/** @noinspection PhpParamsInspection */
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\STTLocator;

use Bungle\Framework\StateMachine\STT\AbstractSTT;
use Bungle\Framework\StateMachine\STTLocator\ContainerSTTLocator;
use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerSTTLocatorTest extends MockeryTestCase
{
    public function testGetSTTForClass(): void
    {
        /** @var class-string<mixed> $entityGoods */
        $entityGoods = 'App\Document\Goods';
        /** @var class-string<mixed> $entityOrder */
        $entityOrder = 'App\Document\Goods\Order';

        $container = Mockery::mock(ContainerInterface::class);
        $locator = new ContainerSTTLocator();
        $locator->setContainer($container);
        $container
            ->expects('get')
            ->with('App\STT\GoodsSTT')
            ->andReturn($goodsStt = Mockery::mock(AbstractSTT::class));
        $container
            ->expects('get')
            ->with('App\STT\Goods\OrderSTT')
            ->andReturn($orderStt = Mockery::mock(AbstractSTT::class));

        self::assertEquals($goodsStt, $locator->getSTTForClass($entityGoods));
        self::assertEquals($orderStt, $locator->getSTTForClass($entityOrder));
    }

    public function testNoContainer(): void
    {
        $this->expectException(LogicException::class);
        $locator = new ContainerSTTLocator();
        /** @var class-string<mixed> */
        $cls = 'App\Document\Foo';
        $locator->getSTTForClass($cls);
    }
}
