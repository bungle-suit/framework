<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute;

use Bungle\Framework\Model\ExtAttribute\AttributeUtils;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AttributeUtilsTest extends MockeryTestCase
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
}
