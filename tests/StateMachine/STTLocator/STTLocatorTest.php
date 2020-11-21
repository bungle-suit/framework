<?php

/** @noinspection PhpParamsInspection */
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\STTLocator;

use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\StateMachine\STT\AbstractSTT;
use Bungle\Framework\StateMachine\STTLocator\STTLocator;
use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\DependencyInjection\Container;

class STTLocatorTest extends MockeryTestCase
{
    private Container $container;
    /** @var EntityRegistry|Mockery\MockInterface|Mockery\LegacyMockInterface */
    private $entityRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        $this->entityRegistry = Mockery::mock(EntityRegistry::class);
        $this->entityRegistry->allows('getHigh')->with(Foo::class)->andReturn('foo');
        $this->entityRegistry->allows('getHigh')->with(Bar::class)->andReturn('bar');
    }

    public function testGetSTTForClass(): void
    {
        $this->container->set('\App\STT\fooSTT', $fooSTT = $this->createMock(AbstractSTT::class));
        $this->container->set('\App\STT\barSTT', $barSTT = $this->createMock(AbstractSTT::class));

        /** @var array<string, class-string<mixed>> */
        $classesByHigh = [
            'foo' => '\App\STT\fooSTT',
            'bar' => '\App\STT\barSTT',
        ];
        $locator = new STTLocator(
            $this->container,
            $this->entityRegistry,
            $classesByHigh,
        );

        self::assertEquals($barSTT, $locator->getSTTForClass(Bar::class));
        self::assertEquals($fooSTT, $locator->getSTTForClass(Foo::class));
    }

    public function testSTTNotFound(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('STT service for '.Foo::class.' not found');

        /** @var array<string, class-string<mixed>> */
        $classesByHigh = ['bar' => '\App\STT\barSTT'];
        $this->container->set('\App\STT\fooSTT', $this->createMock(AbstractSTT::class));
        $locator = new STTLocator(
            $this->container,
            $this->entityRegistry,
            $classesByHigh
        );

        $locator->getSTTForClass(Foo::class);
    }
}
