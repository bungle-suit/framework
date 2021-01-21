<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Model;

use Bungle\Framework\Model\BunglePropertyAccessor;
use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class BunglePropertyAccessorTest extends MockeryTestCase
{
    /** @var Mockery\MockInterface|PropertyAccessorInterface */
    private $inner;
    private BunglePropertyAccessor $propAcc;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inner = Mockery::mock(PropertyAccessorInterface::class);
        $this->propAcc = new BunglePropertyAccessor($this->inner);
    }

    public function testRelay(): void
    {
        $o = (object)[];

        // setValue
        $this->inner->expects('setValue')->with($o, 'blah', 'foo');
        $this->propAcc->setValue($o, 'blah', 'foo');

        // getValue
        $this->inner->expects('getValue')->with($o, 'foo')->andReturn('blah');
        self::assertEquals('blah', $this->propAcc->getValue($o, 'foo'));

        // isWritable
        $this->inner->expects('isWritable')->with($o, 'foo')->andReturnTrue();
        self::assertTrue($this->propAcc->isWritable($o, 'foo'));

        // isReadable
        $this->inner->expects('isReadable')->with($o, 'foo')->andReturnTrue();
        self::assertTrue($this->propAcc->isReadable($o, 'foo'));
    }

    public function testReadSelf(): void
    {
        $o = (object)[];

        self::assertTrue($this->propAcc->isReadable($o, ''));
        self::assertSame($o, $this->propAcc->getValue($o, ''));
    }

    public function testWriteSelf(): void
    {
        $o = (object)[];

        self::assertFalse($this->propAcc->isWritable($o, ''));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('BunglePropertyAccessor: setValue on self');
        $this->propAcc->setValue($o, '', 'foo');
    }
}

