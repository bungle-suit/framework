<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

use RuntimeException;

interface QueryStepInterface
{
    /**
     * @throws RuntimeException encounter user or database error
     */
    public function build(Builder $builder): void;
}
