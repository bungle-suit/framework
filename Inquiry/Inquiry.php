<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

use Doctrine\ODM\MongoDB\DocumentManager;
use const true;

/**
 * Service doing query related operations.
 *
 * Currently only single document class considered.
 * More complex query such as Aggregation, not
 * supported by Inquiry service.
 */
class Inquiry
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Paged query, fetch paged data into array,
     * as well as total records.
     */
    public function paged(QueryBuilderInterface $qb, QueryParams $params): PagedData
    {
        $count = $this->count($qb, $params);
        if (0 == $count) {
            return new PagedData([], 0);
        }

        $data = $this->search($qb, $params);
        if (!is_array($data)) {
            /** @noinspection PhpParamsInspection */
            $data = iterator_to_array($data);
        }

        return new PagedData($data, $count);
    }

    private function count(QueryBuilderInterface $qb, QueryParams $params): int
    {
        $ctx = $this->createContext($params, true)
        foreach ($qb->steps() as $step) {
            call_user_func($step, $ctx);
        }
        return $ctx->getBuilder()->getQuery()->execute();
    }

    /**
     * Returns query result in a stream, count query
     * not performed.
     */
    public function search(QueryBuilderInterface $qb, QueryParams $params): iterable
    {
        $ctx = $this->createContext($params, false);
        foreach ($qb->steps() as $step) {
            call_user_func($step, $ctx);
        }

        return $ctx->getBuilder()->getQuery()->getIterator();
    }

    private function createContext(QueryParams $params, $buildForCount): StepContext
    {
        $builder = $this->dm->createQueryBuilder($params->docClass);
        $builder->readOnly();
        return new StepContext($buildForCount, $params, $builder);
    }
}
