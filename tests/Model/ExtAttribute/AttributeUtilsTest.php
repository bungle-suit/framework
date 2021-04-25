<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute;

use Bungle\Framework\Model\ExtAttribute\AttributeUtils;

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
