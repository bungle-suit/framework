<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute;

use Bungle\Framework\Model\ExtAttribute\AttributeInterface;
use Bungle\Framework\Model\ExtAttribute\AttributeTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AttributeTraitTest extends MockeryTestCase
{
    /** @var AttributeInterface  */
    private $attr;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attr = new class() implements AttributeInterface {
            use AttributeTrait;
        };
    }

    public function testAsBool(): void
    {
        $this->attr->setBool(true);
        self::assertTrue($this->attr->asBool());
        self::assertEquals('1', $this->attr->getValue());

        $this->attr->setBool(false);
        self::assertFalse($this->attr->asBool());
        self::assertEquals('', $this->attr->getValue());

        $this->attr->setValue('');
        self::assertFalse($this->attr->asBool());
    }

    public function testAsInt(): void
    {
        self::assertEquals(0, $this->attr->asInt());

        $this->attr->setInt(100);
        self::assertEquals(100, $this->attr->asInt());
        self::assertSame('100', $this->attr->getValue());

        $this->attr->setValue('abc');
        self::assertEquals(0, $this->attr->asInt());

        $this->attr->setInt(0);
        self::assertSame('', $this->attr->getValue());
    }

    public function testAsFloat(): void
    {
        self::assertSame(0.0, $this->attr->asFloat());

        $this->attr->setFloat(100.5);
        self::assertEquals(100.5, $this->attr->asFloat());
        self::assertSame('100.5', $this->attr->getValue());

        $this->attr->setValue('abc');
        self::assertEquals(0, $this->attr->asFloat());

        $this->attr->setFloat(0);
        self::assertSame('', $this->attr->getValue());
    }

    /** @dataProvider stringArrayProvider */
    public function testStringArray($val, $raw, $back = null): void
    {
        $this->attr->setValue($raw);
        self::assertEquals($val, $this->attr->asStringArray());

        $this->attr->setStringArray($val);
        self::assertEquals($back ?? $raw, $this->attr->getValue());
    }

    public function stringArrayProvider()
    {
        return [
            'default' => [[], ''],
            'trimmed empty' => [[], " \t ", ''],
            'one' => [['one'], 'one'],
            'trimmed one' => [['one'], ' one', 'one'],
            'three' => [['one', 'two', 'three'], 'one,two,three'],
        ];
    }

    /** @dataProvider encodeFloatProvider */
    public function testEncodeFloat($exp, $val): void
    {
        self::assertEquals($exp, $this->attr::encodeFloat($val));
    }

    public function encodeFloatProvider()
    {
        return [
            'zero' => ['', 0.0],
            'non zero' => ['-4.5', -4.5],
        ];
    }
}
