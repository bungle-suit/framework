<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute;

use Bungle\Framework\Model\ExtAttribute\AbstractNormalizedAttributes;
use Bungle\Framework\Model\ExtAttribute\AttributeSetDefinition;
use Bungle\Framework\Model\ExtAttribute\BoolAttribute;
use Bungle\Framework\Model\ExtAttribute\StringAttribute;

class TestNormalizedAttributeSet extends AbstractNormalizedAttributes
{
    protected static function createDefinition(): AttributeSetDefinition
    {
        return new AttributeSetDefinition(
            [
                new BoolAttribute('a', 'Foo', 'Foo Desc'),
                new BoolAttribute('b', 'Bar', 'Bar Desc'),
                new StringAttribute('c', 'Foobar', 'Foobar Desc'),
            ],
            fn(string $name) => new TestAttribute($name)
        );
    }
}
