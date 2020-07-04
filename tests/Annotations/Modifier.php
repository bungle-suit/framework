<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Annotations;

use DateTime;

trait Modifier
{
    protected string $modifier;

    private DateTime $modifyTime;
}
