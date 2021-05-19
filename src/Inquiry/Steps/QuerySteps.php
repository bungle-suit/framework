<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry\Steps;

use Bungle\Framework\Inquiry\Builder;
use Bungle\Framework\Inquiry\QueryStepInterface;

class QuerySteps
{
    // Default page record count.
    public const PAGE_RECS = 25;

    /**
     * Apply $inner step only if current is build for count.
     * @param QueryStepInterface|callable(Builder): void $inner
     * @return callable(Builder): void
     */
    public static function ifBuildForCount($inner): callable
    {
        return function (Builder $builder) use ($inner): void {
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
     * Included in Query::createExtraCountSteps(), no need to explicit include
     * it if not override createExtraCountSteps().
     */
    public static function buildCount(Builder $builder): void
    {
        $builder->getQueryBuilder()->resetDQLPart('orderBy');
    }

    /**
     * Change query limit/offset for paging.
     *
     * Included in Query::createExtraPagingSteps(), no need to explicit include
     * it if not override createExtraPagingSteps().
     */
    public static function buildPaging(Builder $builder): void
    {
        $pageNo = $builder->getQueryParams()->getPageNo();
        $builder->getQueryBuilder()->setFirstResult($pageNo * self::PAGE_RECS);
        $builder->getQueryBuilder()->setMaxResults(self::PAGE_RECS);
    }
}
