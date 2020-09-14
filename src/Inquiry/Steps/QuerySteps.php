<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry\Steps;

use Bungle\Framework\Inquiry\Builder;
use Bungle\Framework\Inquiry\QueryStepInterface;

class QuerySteps
{
    /**
     * Apply $inner step only if current is build for count.
     * @param QueryStepInterface|callable(Builder): void $inner
     * @return callable(Builder): void
     */
    public static function ifBuildForCount($inner): callable
    {
        return function (Builder $builder) use ($inner) {
            if ($builder->isBuildForCount()) {
                $inner($builder);
            }
        };
    }

    /**
     * Change the current query into count:
     *
     * 1. replace select list to 'count(0) as "_count"'
     * 2. remove order by sql part.
     *
     * Should wrap in ifBuildForCount()
     */
    public static function buildCount(Builder $builder): void
    {
        $builder->getQueryBuilder()->add('select', ['count(0) as _count']);
        $builder->getQueryBuilder()->resetDQLPart('orderBy');
    }
}
