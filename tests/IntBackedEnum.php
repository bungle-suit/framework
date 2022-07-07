<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests;

enum IntBackedEnum: int
{
    case foo = 1;
    case bar = 2;
    case baz = 3;
}
