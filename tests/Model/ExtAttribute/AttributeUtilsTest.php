<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute;

use Bungle\Framework\Model\ExtAttribute\AttributeDefinitionInterface;
use Bungle\Framework\Model\ExtAttribute\AttributeUtils;
use Mockery;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class AttributeUtilsTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    public function testGetBoolAttribute(): void
    {
        // attribute not exist
        self::assertFalse(AttributeUtils::getBoolAttribute([], 'foo'));

        // attribute exist but false
        $attr = new TestAttribute('foo', '');
        self::assertFalse(AttributeUtils::getBoolAttribute([$attr], 'foo'));

        // attribute exist but true
        $attr->setBool(true);
        self::assertTrue(AttributeUtils::getBoolAttribute([$attr], 'foo'));
    }

    public function testGetFloatAttribute(): void
    {
        // attribute not exist
        self::assertSame(0.0, AttributeUtils::getFloatAttribute([], 'foo'));

        // attribute exist but false
        $attr = new TestAttribute('foo', '123.45');
        self::assertSame(123.45, AttributeUtils::getFloatAttribute([$attr], 'foo'));
    }

    public function testAddForm(): void
    {
        $fb = Mockery::mock(FormBuilderInterface::class);
        $def = Mockery::mock(AttributeDefinitionInterface::class);
        $def->expects('getLabel')->andReturn('lbl');
        $def->expects('getName')->andReturn('field');
        $def->expects('getDescription')->andReturn('helps')->twice();
        $def->expects('getFormOption')->andReturn(['any' => 'option', 'required' => 'overwrite']);
        $def->expects('getFormType')->andReturn(IntegerType::class);
        $fb->expects('add')->with('field', IntegerType::class, [
            'label' => 'lbl',
            'required' => 'overwrite',
            'help' => 'helps',
            'any' => 'option',
        ]);
        AttributeUtils::addForm($fb, $def);
    }
}
