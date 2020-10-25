<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute;

use Bungle\Framework\Model\ExtAttribute\AttributeSetDefinition;
use Bungle\Framework\Model\ExtAttribute\BoolAttribute;
use Bungle\Framework\Model\ExtAttribute\DataMapper\AbstractNormalizedAttributes;
use Bungle\Framework\Model\ExtAttribute\StringAttribute;

class TestNormalizedAttributeSet extends AbstractNormalizedAttributes
{
    protected static function createDefinition(): AttributeSetDefinition
    {
        return new AttributeSetDefinition(
            [
                new BoolAttribute('a', 'Foo', ''),
                new BoolAttribute('b', 'Bar', ''),
                new StringAttribute('c', 'Foobar', ''),
            ],
            fn(string $name) => new TestAttribute($name)
        );
    }
}
