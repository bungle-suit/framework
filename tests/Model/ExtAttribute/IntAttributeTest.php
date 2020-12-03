<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute;

use Bungle\Framework\Model\ExtAttribute\IntAttribute;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class IntAttributeTest extends MockeryTestCase
{
    private IntAttribute $def;

    protected function setUp(): void
    {
        parent::setUp();

        $this->def = new IntAttribute('foo', 'blah');
    }

    public function testFormType(): void
    {
        self::assertEquals(IntegerType::class, $this->def->getFormType());
    }

    public function testCreateDefault(): void
    {
        self::assertSame(0, $this->def->createDefault());
    }

    public function testRestoreValue(): void
    {
        $attr = new TestAttribute('foo');
        self::assertEquals(0, $this->def->restoreValue($attr));

        $attr->setValue('123');
        self::assertEquals(123, $this->def->restoreValue($attr));

        $attr->setValue('124.5');
        self::assertEquals(124, $this->def->restoreValue($attr));
    }

    public function testSaveValue(): void
    {
        $attr = new TestAttribute('foo');
        $this->def->saveValue($attr, 0);
        self::assertEquals('', $attr->getValue());

        $this->def->saveValue($attr, 123);
        self::assertEquals('123', $attr->getValue());
    }
}
