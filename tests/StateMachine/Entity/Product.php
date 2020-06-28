<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\Entity;

use Bungle\Framework\Annotation\High;
use Bungle\Framework\Entity\CommonTraits\AutoIncID;
use Bungle\Framework\Entity\CommonTraits\Stateful;
use Bungle\Framework\Entity\CommonTraits\StatefulInterface;

/**
 * @High("prd")
 */
class Product implements StatefulInterface
{
    use AutoIncID;
    use Stateful;
    public string $code;
    public string $name;
}
