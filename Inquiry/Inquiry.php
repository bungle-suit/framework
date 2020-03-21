<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

/**
 * Service doing query related operations.
 *
 * Currently only single document class considered.
 * More complex query such as Aggregation, not
 * supported by Inquiry service.
 */
class Inquiry
{
    private DBProviderInterface $db;

    public function __construct(DBProviderInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Paged query, fetch paged data into array,
     * as well as total records.
     */
    public function paged(QueryBuilderInterface $qb, QueryParams $params): PagedData
    {
        $ctx = new StepContext(true, $params);
        foreach ($qb->steps() as $step) {
            call_user_func($step, $ctx);
        }

        $count = $this->db->count($ctx->query);
        if (0 == $count) {
            return new PagedData([], 0);
        }

        $data = $this->search($qb, $params);
        if (!is_array($data)) {
            $data = iterator_to_array($data);
        }

        return new PagedData($data, $count);
    }

    /**
     * Returns query result in a stream, count query
     * not performed.
     */
    public function search(QueryBuilderInterface $qb, QueryParams $params): iterable
    {
        $ctx = new StepContext(false, $params);
        foreach ($qb->steps() as $step) {
            call_user_func($step, $ctx);
        }

        return $this->db->search($ctx->query);
    }

}
