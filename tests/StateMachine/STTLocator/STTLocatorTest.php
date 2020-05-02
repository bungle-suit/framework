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
    private string $fooCls;
    private string $barCls;
    /** @var EntityRegistry|Mockery\MockInterface|Mockery\LegacyMockInterface */
    private $entityRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        list($this->fooCls, $this->barCls) = ['\App\Document\Foo', '\App\Document\Bar'];
        $this->entityRegistry = Mockery::mock(EntityRegistry::class);
        $this->entityRegistry->allows('getHigh')->with($this->fooCls)->andReturn('foo');
        $this->entityRegistry->allows('getHigh')->with($this->barCls)->andReturn('bar');
    }

    public function testGetSTTForClass()
    {
        $this->container->set('\App\STT\fooSTT', $fooSTT = $this->createMock(AbstractSTT::class));
        $this->container->set('\App\STT\barSTT', $barSTT = $this->createMock(AbstractSTT::class));

        $locator = new STTLocator($this->container, $this->entityRegistry, [
            'foo' => '\App\STT\fooSTT',
            'bar' => '\App\STT\barSTT',
        ]);

        self::assertEquals($barSTT, $locator->getSTTForClass($this->barCls));
        self::assertEquals($fooSTT, $locator->getSTTForClass($this->fooCls));
    }

    public function testSTTNotFound(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("STT service for {$this->fooCls} not found");

        $this->container->set('\App\STT\fooSTT', $this->createMock(AbstractSTT::class));
        $locator = new STTLocator($this->container, $this->entityRegistry, [ 'bar' => '\App\STT\barSTT' ]);

        $locator->getSTTForClass($this->fooCls);
    }
}
