<?php
declare(strict_types=1);

namespace Bungle\Framework\Entity\CommonTraits;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Use it to implement StatefulInterface
 */
trait Stateful
{
    /* @ODM\String */
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
