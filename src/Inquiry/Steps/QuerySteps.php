<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry\Steps;

use Aura\SqlQuery\Common\SelectInterface;
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
        $qb = $builder->getQueryBuilder();
        if ($qb instanceof SelectInterface) {
            $qb->offset($pageNo * self::PAGE_RECS);
            $qb->limit(self::PAGE_RECS);
        } else {
            $qb->setFirstResult($pageNo * self::PAGE_RECS);
            $qb->setMaxResults(self::PAGE_RECS);
        }
    }
}
