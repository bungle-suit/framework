<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute;

use Bungle\Framework\Model\ExtAttribute\AttributeInterface;
use Bungle\Framework\Model\ExtAttribute\AttributeTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AttributeTraitTest extends MockeryTestCase
{
    public function testAsBool(): void
    {
        $attr = new class() implements AttributeInterface {
            use AttributeTrait;
        };

        $attr->setBool(true);
        self::assertTrue($attr->asBool());
        self::assertEquals('1', $attr->getValue());

        $attr->setBool(false);
        self::assertFalse($attr->asBool());
        self::assertEquals('', $attr->getValue());

        $attr->setValue('');
        self::assertFalse($attr->asBool());
    }
}
