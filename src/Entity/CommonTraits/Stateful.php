<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity\CommonTraits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Use it to implement StatefulInterface.
 */
trait Stateful
{
    /**
     * 状态
     *
     * @ORM\Column()
     */
    protected string $state = StatefulInterface::INITIAL_STATE;

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $v): void
    {
        $this->state = $v;
    }
}
