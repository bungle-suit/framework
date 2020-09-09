<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute;

use Bungle\Framework\Model\ExtAttribute\AttributeDefinitionInterface;
use Bungle\Framework\Model\ExtAttribute\AttributeInterface;
use Bungle\Framework\Model\ExtAttribute\AttributeSet;
use Bungle\Framework\Model\ExtAttribute\AttributeTrait;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AttributeSetTest extends MockeryTestCase
{
    private AttributeSet $set;
    private $def1;
    private $def2;
    private $def3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->def1 = Mockery::mock(AttributeDefinitionInterface::class);
        $this->def2 = Mockery::mock(AttributeDefinitionInterface::class);
        $this->def3 = Mockery::mock(AttributeDefinitionInterface::class);

        $this->def1->allows('getName')->andReturn('foo');
        $this->def2->allows('getName')->andReturn('bar');
        $this->def3->allows('getName')->andReturn('foobar');
        $this->def1->allows('createDefault')->andReturn(1);
        $this->def2->allows('createDefault')->andReturn(2);
        $this->def3->allows('createDefault')->andReturn('');

        $this->set = new AttributeSet([$this->def1, $this->def2, $this->def3]);
    }

    public function testGetDefinitions(): void
    {
        self::assertEquals([
            'foo' => $this->def1,
            'bar' => $this->def2,
            'foobar' => $this->def3,
        ], $this->set->getDefinitions());
    }

    public function testFromAttributes(): void
    {
        self::assertEquals(['foo' => 1, 'bar' => 2, 'foobar' => ''], $this->set->initDataSet());
    }

    public function testRestoreAttrs(): void
    {
        $attrValue1 = self::newAttribute('bar', '3');
        $this->def2->expects('restoreValue')->with($attrValue1)->andReturn(3);
        self::assertEquals(
            ['foo' => 1, 'bar' => 3, 'foobar' => ''],
            $this->set->fromAttributes([$attrValue1])
        );
    }

    public function testToAttributes(): void
    {
        $set = new AttributeSet([$this->def1]);

        // case 1: value change to non-default
        $attr = self::newAttribute('foo', '4');
        $this->def1
            ->expects('saveValue')->with($attr, '5')
            ->andReturnUsing(function (AttributeInterface $attr, $value) {
                $attr->setValue($value);
                return null;
            })
        ;
        self::assertEquals(
            [$attr],
            $set->toAttributes(fn(string $attrName) => self::newAttribute($attrName), [$attr], ['foo' => '5'])
        );
        self::assertEquals('5', $attr->getValue());

        // case 2: value change to default
        $this->def1
            ->expects('saveValue')->with($attr, '')
            ->andReturnUsing(function (AttributeInterface $attr, $value) {
                $attr->setValue($value);
                return null;
            })
        ;
        self::assertEquals(
            [$attr],
            $set->toAttributes(fn(string $attrName) => self::newAttribute($attrName), [$attr], ['foo' => ''])
        );
        self::assertEquals('', $attr->getValue());

        // case 3: attribute not exist, new value is default
        $this->def1
            ->expects('saveValue')->with(Mockery::type(AttributeInterface::class), '')
            ->andReturnUsing(function (AttributeInterface $attr, $value) {
                $attr->setValue($value);
                return null;
            })
        ;
        self::assertEquals(
            [],
            $set->toAttributes(fn(string $attrName) => self::newAttribute($attrName), [], ['foo' => ''])
        );
        self::assertEquals('', $attr->getValue());

        // case 4: attribute not exist, new value not default
        $this->def1
            ->expects('saveValue')->with(Mockery::type(AttributeInterface::class), '5')
            ->andReturnUsing(function (AttributeInterface $attr, $value) {
                $attr->setValue($value);
                return null;
            })
        ;
        $r = $set->toAttributes(fn(string $attrName) => self::newAttribute($attrName), [], ['foo' => '5']);
        self::assertCount(1, $r);
        /** @var AttributeInterface $newAttr */
        $newAttr = $r[0];
        self::assertEquals('foo', $newAttr->getAttribute());
        self::assertEquals('5', $newAttr->getValue());
    }

    public static function newAttribute(string $attrName, string $value = ''): AttributeInterface
    {
        return new class($attrName, $value) implements AttributeInterface {
            use AttributeTrait;

            public function __construct(string $attribute, string $value)
            {
                $this->attribute = $attribute;
                $this->value = $value;
            }
        };
    }
}
