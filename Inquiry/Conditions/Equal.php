<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry\Conditions;

use Bungle\Framework\Inquiry\ConditionInterface;
use Doctrine\ODM\MongoDB\Query\Builder;

class Equal implements ConditionInterface
{
    /**
     * @var mixed the value to equal
     */
    private $qbeValue;

    public function __construct($qbeValue)
    {
        $this->qbeValue = $qbeValue;
    }

    public function build(Builder $q): void
    {
        $q->equals($this->qbeValue);
    }
}
