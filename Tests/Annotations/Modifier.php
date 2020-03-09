<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Annotations;

use Bungle\Framework\Annotation\LogicName;

trait Modifier
{
    /**
     * @LogicName("修改人")
     */
    protected string $modifier;

    /**
     * @LogicName("修改时间")
     */
    private \Date $modifyTime;
}
