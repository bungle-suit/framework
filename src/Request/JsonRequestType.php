<?php

declare(strict_types=1);

namespace Bungle\Framework\Request;

use Attribute;

/**
 * Specify symfony serializer deserialize type string, useful for array type.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class JsonRequestType
{
    public function __construct(public string $type)
    {
    }
}
