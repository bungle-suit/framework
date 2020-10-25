<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute;

use Bungle\Framework\Model\ExtAttribute\AttributeInterface;
use Bungle\Framework\Model\ExtAttribute\AttributeTrait;

class TestAttribute implements AttributeInterface
{
    use AttributeTrait;

    public function __construct(string $attribute, string $value = '')
    {
        $this->attribute = $attribute;
        $this->value = $value;
    }
}
