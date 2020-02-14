<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\Entity;

use Bungle\Framework\Annotation\HighPrefix;
use PHPUnit\Framework\TestCase;
use Bungle\Framework\StateMachine\Entity;

/**
 * @HighPrefix("ord")
 */
class Order extends Entity
{
    public string $code;
}
