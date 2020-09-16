<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

use Bungle\Framework\Inquiry\Steps\QuerySteps;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use LogicException;
use Traversable;

class Query
{
    /** @var array<string, ColumnMeta> */
    private array $columns;
    /** @var array<string, QBEMeta> */
    private array $qbeMetas;

    /**
     * @var QueryStepInterface[] $steps ;
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
        return $this->queryData($params, false);
    }

    private function queryData(QueryParams $params, bool $paging): Traversable
    {
        $qb = $this->prepareQuery($params, false, $paging);
        foreach ($qb->getQuery()
                    ->iterate(null, AbstractQuery::HYDRATE_ARRAY) as $rows) {
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
        $data = iterator_to_array($this->queryData($params, true), false);
        $count = $this->queryCount($params);

        return new PagedData($data, $count);
    }

    private function queryCount(QueryParams $params): int
    {
        $qb = $this->prepareQuery($params, true, false);

        return intval(
            $qb->getQuery()
               ->execute(null, AbstractQuery::HYDRATE_SINGLE_SCALAR)
        );
    }

    private function prepareQuery(
        QueryParams $params,
        bool $forCount,
        bool $pagedData
    ): QueryBuilder {
        $qb = $this->em->createQueryBuilder();
        $builder = new Builder($qb, $params);
        $steps = $this->steps;
        if ($forCount) {
            $builder->set(Builder::ATTR_BUILD_FOR_COUNT, true);
            $steps = array_merge($steps, $this->createExtraCountSteps());
        }
        if ($pagedData) {
            $steps = array_merge($steps, $this->createExtraPagingSteps());
        }

        foreach ($steps as $step) {
            $step($builder);
        }
        if (!$forCount) {
            $this->columns = $builder->getColumns();
            $this->qbeMetas = $builder->getQBEs();
        }

        return $qb;
    }

    /**
     * In pagedQuery(), these steps will appended to steps to build count query.
     *
     * Normally no need to override, default implementation can handle most cases.
     * @return QueryStepInterface[]
     */
    protected function createExtraCountSteps(): array
    {
        return [
            [QuerySteps::class, 'buildCount'],
        ];
    }

    /**
     * In pagedQuery(), these steps will appended to steps to build data query.
     *
     * Normally no need to override, default implementation can handle most cases.
     * @return QueryStepInterface[]
     */
    protected function createExtraPagingSteps(): array
    {
        return [
            [QuerySteps::class, 'buildPaging'],
        ];
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

    /**
     * @return QBEMeta[]
     */
    public function getQBEMetas(): array
    {
        return $this->qbeMetas;
    }
}
