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

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Build query steps, should be called after construct immediately.
     * @phpstan-param QueryStepInterface[]|callable(): iterable<QueryStepInterface> $iterSteps
     *                  steps array or steps builder.
     */
    public function buildSteps($stepsOrBuilder): void
    {
        if (isset($this->steps)) {
            throw new LogicException('steps already build');
        }

        if (is_array($stepsOrBuilder)) {
            $this->steps = $stepsOrBuilder;
        } else {
            $this->steps = iterator_to_array($stepsOrBuilder(), false);
        }
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

        foreach ($this->getSteps() as $step) {
            $step($builder);
        }
        if (!$forCount) {
            $this->columns = $builder->getColumns();
        }
        return $qb;
    }

    /**
     * @return QueryStepInterface[]
     */
    public function getSteps(): array
    {
        if (!isset($this->steps)) {
            throw new LogicException('query step not build, call buildSteps() first');
        }
        return $this->steps;
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
