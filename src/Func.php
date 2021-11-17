<?php

declare(strict_types=1);

namespace Bungle\Framework;

interface Func
{
    public function __invoke(mixed ...$args): mixed;
}
