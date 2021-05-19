<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry\Steps;

use Bungle\Framework\Inquiry\Builder;

class QuerySteps
{
    // Default page record count.
    public const PAGE_RECS = 25;

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
