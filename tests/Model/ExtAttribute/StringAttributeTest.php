<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute;

use Bungle\Framework\Model\ExtAttribute\StringAttribute;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class StringAttributeTest extends MockeryTestCase
{
    public function testGetFormOption(): void
    {
        $attr = new StringAttribute('', '', '');
        self::assertEquals(['empty_data' => ''], $attr->getFormOption());
    }
}
