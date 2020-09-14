<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

use Bungle\Framework\Model\HasAttributes;
use Bungle\Framework\Model\HasAttributesInterface;
use Doctrine\ORM\QueryBuilder;
use LogicException;

/**
 * QueryStepInterface use Builder to build the query.
 */
class Builder implements HasAttributesInterface
{
    use HasAttributes;

    private QueryBuilder $qb;
    private QueryParams $queryParams;
    /** @var ColumnMeta[] */
    private array $columns = [];

    private const AUTO_COLUMN_PREFIX = '__col_';
    private int $autoColIdx = 0;

    /**
     * @param array<string, mixed> $options the value used to init attributes.
     */
    public function __construct(QueryBuilder $qb, QueryParams $queryParams)
    {
        $this->qb = $qb;
        $this->queryParams = $queryParams;
        $this->initAttributes($queryParams->getOptions());
    }

    /**
     * @param string $name column name, each column must have unique name, create random unique name if $name is empty.
     * @return string return column name.
     */
    public function addColumn(ColumnMeta $column, string $name = ''): string
    {
        if ($name === '') {
            $name = self::AUTO_COLUMN_PREFIX.(++$this->autoColIdx);
        }
        if (key_exists($name, $this->columns)) {
            throw new LogicException("Column \"$name\" already added");
        }
        $this->columns[$name] = $column;

        return $name;
    }

    /**
     * @return array<string, ColumnMeta>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->qb;
    }

    public function getQueryParams(): QueryParams
    {
        return $this->queryParams;
    }
}
