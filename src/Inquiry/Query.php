<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

use Bungle\Framework\Inquiry\Steps\QuerySteps;
use Doctrine\ORM\AbstractQuery;
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
     * @phpstan-var (callable(Builder): void)[]
     */
    private array $steps;

    private EntityManagerInterface $em;
    private string $title;

    /**
     * @phpstan-param array<callable(Builder): void> $steps
     */
    public function __construct(
        EntityManagerInterface $em,
        array $steps,
        string $title = 'Query Name Not Set'
    ) {
        $this->em = $em;
        $this->steps = $steps;
        $this->title = $title;
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
     * @return Traversable<int, mixed[]>
     */
    public function query(QueryParams $params): Traversable
    {
        $qb = $this->prepareQuery($params, self::BUILD_FOR_DATA)->getQueryBuilder();

        return $this->queryData($qb);
    }

    /**
     * @return Traversable<array<mixed>>
     */
    protected function queryData(QueryBuilder $qb): Traversable
    {
        foreach (
            $qb->getQuery()
               ->iterate(null, AbstractQuery::HYDRATE_ARRAY) as $rows
        ) {
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
        Assert::false($this->nativeMode, 'Not support paged query when in native modelf::');

        $qb = $this->prepareQuery($params, self::BUILD_FOR_PAGING)->getQueryBuilder();
        $pager = new Paginator($qb->getQuery());
        $pager->setUseOutputWalkers(false);
        $count = count($pager);
        $data = iterator_to_array($pager, false);

        return new PagedData($data, $count);
    }

    private const BUILD_FOR_PAGING = 2;
    private const BUILD_FOR_QBE = 3;
    private const BUILD_FOR_DATA = 4; // triggered by self::query() method

    private function prepareQuery(QueryParams $params, int $buildFor): Builder
    {
        $qb = $this->em->createQueryBuilder();
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
}
