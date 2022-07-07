<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests;

enum StringBackedEnum: string
{
    case foo = 'foo';
    case bar = 'bar';
    case baz = 'baz';
}
