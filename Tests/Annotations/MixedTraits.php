<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Annotations;

use Bungle\Framework\Annotation\LogicName;

class MixedTraits
{
    use Modifier;

    /**
     * @LogicName("数量")
     */
    public int $count;
}
