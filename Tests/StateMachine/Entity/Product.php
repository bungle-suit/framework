<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\Entity;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Bungle\Framework\Entity\CommonTraits\Stateful;
use Bungle\Framework\Entity\CommonTraits\ObjectID;
use Bungle\Framework\Annotation\HighPrefix;

/**
 * @HighPrefix("prd")
 */
class Product implements StatefulInterface
{
    use ObjectID, Stateful;
    public string $code;
    public string $name;
}
