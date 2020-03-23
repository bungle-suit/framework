<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Annotations;

use Bungle\Framework\Annotation\LogicName;

class Derived extends Entity
{
    /**
     * @LogicName("New Counter")
     */
    public int $count;

    /**
     * @LogicName("地址")
     */
    public string $address;
}
