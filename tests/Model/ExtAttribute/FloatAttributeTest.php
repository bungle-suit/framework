<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute;

use Bungle\Framework\Model\ExtAttribute\FloatAttribute;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class FloatAttributeTest extends TestCase
{
    private FloatAttribute $def;

    protected function setUp(): void
    {
        parent::setUp();

        $this->def = new FloatAttribute('foo', 'blah');
    }

    public function testFormType(): void
    {
        self::assertEquals(NumberType::class, $this->def->getFormType());
    }

    public function testCreateDefault(): void
    {
        self::assertSame(0.0, $this->def->createDefault());
    }

    public function testRestoreValue(): void
    {
        $attr = new TestAttribute('foo');
        self::assertSame(0.0, $this->def->restoreValue($attr));

        $attr->setValue('234.56');
        self::assertSame(234.56, $this->def->restoreValue($attr));
    }

    public function testSaveValue(): void
    {
        $attr = new TestAttribute('foo');
        $this->def->saveValue($attr, 0);
        self::assertEquals('', $attr->getValue());

        $this->def->saveValue($attr, 123.45);
        self::assertEquals('123.45', $attr->getValue());
    }
}
