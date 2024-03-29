<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\QueryFactory;
use Bungle\Framework\Inquiry\Steps\QuerySteps;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use LogicException;
use Traversable;
use Webmozart\Assert\Assert;

class Query
{
    /** @var array<string, ColumnMeta> */
    private array $columns;
    /** @var array<string, QBEMeta> */
    private array $qbeMetas;
    private bool $nativeMode = false;

    /**
     * @phpstan-param array<callable(Builder): void> $steps
     */
    public function __construct(
        protected EntityManagerInterface $em,
        private readonly array $steps,
        private string $title = 'Query Name Not Set'
    ) {
    }

    /**
     * Build qbe metas. It should be called before query/pagedQuery.
     * @return array<string, QBEMeta>
     */
    public function buildQBEMetas(QueryParams $params): array
    {
        if (isset($this->qbeMetas)) {
            throw new LogicException("QBEs already built");
        }

        $builder = $this->prepareQuery($params, self::BUILD_FOR_QBE);

        return $this->qbeMetas = $builder->getQBEs();
    }

    /**
     * Query data.
     *
     * NOTE: use query step to control weather page no take cared.
     * @return Traversable<int, array>
     */
    public function query(QueryParams $params): Traversable
    {
        $qb = $this->prepareQuery($params, self::BUILD_FOR_DATA)->getQueryBuilder();

        return $this->queryData($qb);
    }

    protected function queryData(QueryBuilder|SelectInterface $qb): Traversable
    {
        if ($qb instanceof SelectInterface) {
            $iter = $this->em->getConnection()
                             ->executeQuery($qb->getStatement(), $qb->getBindValues());
            foreach ($iter as $row) {
                yield $row;
            }

            return;
        }

        foreach ($qb->getQuery()->toIterable() as $row) {
            yield $row;
        }
    }

    /**
     * Query current page data.
     *
     * NOTE: use query step to control weather page no take cared.
     */
    public function pagedQuery(QueryParams $params): PagedData
    {
        if ($this->isNativeMode()) {
            $count = $this->count($params);
            $data = iterator_to_array($this->nativePageQuery($params));
        } else {
            $pager = $this->getPager($params);
            $count = count($pager);
            $data = iterator_to_array($pager, false);
        }

        return new PagedData($data, $count);
    }

    public function count(QueryParams $params): int
    {
        if ($this->isNativeMode()) {
            return $this->countNative($params);
        }

        $pager = $this->getPager($params);

        return count($pager);
    }

    private function countNative(QueryParams $params): int
    {
        $qb = $this->prepareQuery($params, self::BUILD_FOR_COUNT)->getQueryBuilder();

        return (int)($this->em->getConnection()
                              ->fetchOne($qb->getStatement(), $qb->getBindValues()));
    }

    private const BUILD_FOR_PAGING = 2;
    private const BUILD_FOR_QBE = 3;
    private const BUILD_FOR_DATA = 4; // triggered by self::query() method
    private const BUILD_FOR_COUNT = 5; // used in native mode to build count query

    /**
     * initialize internal state, esp, getColumns()
     */
    public function prepare(QueryParams $params): void
    {
        $this->prepareQuery($params, self::BUILD_FOR_DATA);
    }

    private function prepareQuery(QueryParams $params, int $buildFor): Builder
    {
        if ($this->nativeMode) {
            $qb = new QueryFactory('mysql');
        } else {
            $qb = $this->em->createQueryBuilder();
        }

        $builder = new Builder($qb, $params);
        $steps = $this->steps;
        switch ($buildFor) {
            case self::BUILD_FOR_PAGING:
                $steps = array_merge($steps, $this->createExtraPagingSteps());
                break;
            case self::BUILD_FOR_QBE:
                $builder->set(Builder::ATTR_BUILD_FOR_QBE, true);
                break;
            case self::BUILD_FOR_DATA:
                break;
            case self::BUILD_FOR_COUNT:
                $builder->set(Builder::ATTR_BUILD_FOR_COUNT, true);
                break;
            default:
                throw new LogicException("Unknown build type: $buildFor");
        }

        foreach ($steps as $step) {
            $step($builder);
        }

        switch ($buildFor) {
            case self::BUILD_FOR_PAGING:
            case self::BUILD_FOR_DATA:
                $this->columns = $builder->getColumns();
                break;
            case self::BUILD_FOR_QBE:
                $this->qbeMetas = $builder->getQBEs();
                break;
        }

        return $builder;
    }

    /**
     * In pagedQuery(), these steps will appended to steps to build data query.
     *
     * Normally no need to override, default implementation can handle most cases.
     * @phpstan-return (callable(Builder): void)[]
     */
    protected function createExtraPagingSteps(): array
    {
        return [
            QuerySteps::buildPaging(...),
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
     * @return array<string, QBEMeta>
     */
    public function getQBEMetas(): array
    {
        return $this->qbeMetas;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * In native mode, use doctrine DBAL instead of ORM
     */
    public function isNativeMode(): bool
    {
        return $this->nativeMode;
    }

    /**
     * @param bool $nativeMode
     */
    public function setNativeMode(bool $nativeMode): void
    {
        $this->nativeMode = $nativeMode;
    }

    private function getPager(QueryParams $params): Paginator
    {
        Assert::false($this->nativeMode, 'Not support paged query when in native mode');

        $qb = $this->prepareQuery($params, self::BUILD_FOR_PAGING)->getQueryBuilder();
        $pager = new Paginator($qb->getQuery());
        $pager->setUseOutputWalkers(false);

        return $pager;
    }

    private function nativePageQuery(QueryParams $params)
    {
        $qb = $this->prepareQuery($params, self::BUILD_FOR_PAGING)->getQueryBuilder();

        return $this->queryData($qb);
    }
}
