<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

use Doctrine\ODM\MongoDB\Query\Builder;

interface ConditionInterface
{
    public function build(Builder $q): void;
}
