<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\Entity;

use Bungle\Framework\StateMachine\Entity;
use Bungle\Framework\Annotation\HighPrefix;

/**
 * @HighPrefix("prd")
 */
class Product extends Entity
{
    public string $code;
    public string $name;
}
