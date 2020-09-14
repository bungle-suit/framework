<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use LogicException;
use Traversable;

class Query
{
    /** @var array<string, ColumnMeta> */
    private array $columns;

    /**
     * @var QueryStepInterface[] $steps;
     */
    private array $steps;

    private EntityManagerInterface $em;

    /**
     * @phpstan-param QueryStepInterface[] $iterSteps
     */
    public function __construct(EntityManagerInterface $em, array $steps)
    {
        $this->em = $em;
        $this->steps = $steps;
    }

    /**
     * Query data.
     *
     * NOTE: use query step to control weather page no take cared.
     * @return Traversable<int, mixed[]>
     */
    public function query(QueryParams $params): Traversable
    {
        $qb = $this->prepareQuery($params, false);
        foreach ($qb->getQuery()->iterate(null, AbstractQuery::HYDRATE_ARRAY) as $rows) {
            yield from $rows;
        }
    }

    /**
     * Query current page data.
     *
     * NOTE: use query step to control weather page no take cared.
     */
    public function pagedQuery(QueryParams $params): PagedData
    {
        $data = iterator_to_array($this->query($params), false);
        $count = $this->queryCount($params);
        return new PagedData($data, $count);
    }

    private function queryCount(QueryParams $params): int
    {
        $qb = $this->prepareQuery($params, true);
        return $qb->getQuery()->execute(null, AbstractQuery::HYDRATE_SINGLE_SCALAR);
    }

    private function prepareQuery(QueryParams $params, bool $forCount): QueryBuilder
    {
        $qb = $this->em->createQueryBuilder();
        $builder = new Builder($qb, $params);
        if ($forCount) {
            $builder->set(Builder::ATTR_BUILD_FOR_COUNT, true);
        }

        foreach ($this->steps as $step) {
            $step($builder);
        }
        if (!$forCount) {
            $this->columns = $builder->getColumns();
        }
        return $qb;
    }

    /**
     * Describe columns of query result data set.
     * @return array<string, ColumnMeta>
     */
    public function getColumns(): array
    {
        if (!isset($this->columns)) {
            throw new LogicException('columns not exist, until query/pagedQuery()');
        }
        return $this->columns;
    }
}
