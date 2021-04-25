<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute;

use Bungle\Framework\Model\ExtAttribute\AttributeDefinitionInterface;
use Bungle\Framework\Model\ExtAttribute\AttributeUtils;
use Mockery;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

it('get bool attribute', function () {
    // attribute not exist
    expect(AttributeUtils::getBoolAttribute([], 'foo'))->toBeFalse();

    // attribute exist but false
    $attr = new TestAttribute('foo', '');
    expect(AttributeUtils::getBoolAttribute([$attr], 'foo'))->toBeFalse();

    // attribute exist but true
    $attr->setBool(true);
    expect(AttributeUtils::getBoolAttribute([$attr], 'foo'))->toBeTrue();
});

it('get float attribute', function () {
    // attribute not exist
    expect(AttributeUtils::getFloatAttribute([], 'foo'))->toBe(0.0);

    // attribute exist but false
    $attr = new TestAttribute('foo', '123.45');
    expect(AttributeUtils::getFloatAttribute([$attr], 'foo'))->toBe(123.45);
});

it('add form', function () {
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
});
