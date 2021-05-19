<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

use Bungle\Framework\Ent\Code\UniqueName;
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
    /** @var array<string, QBEMeta> */
    private array $qbeMetas = [];

    /** Attribute set to true if current is build for QBEs */
    public const ATTR_BUILD_FOR_QBE = '__build_qbe__';
    private const AUTO_COLUMN_PREFIX = '__col_';
    private UniqueName $autoColName;

    public function __construct(QueryBuilder $qb, QueryParams $queryParams)
    {
        $this->qb = $qb;
        $this->queryParams = $queryParams;
        $this->initAttributes($queryParams->getOptions());
        $this->autoColName = new UniqueName(self::AUTO_COLUMN_PREFIX);
    }

    /**
     * @param string $name column name, each column must have unique name, create random unique
     *     name if $name is empty.
     * @return string return column name.
     */
    public function addColumn(ColumnMeta $column, string $name = ''): string
    {
        if ($name === '') {
            $name = $this->autoColName->next();
        }
        if (key_exists($name, $this->columns)) {
            throw new LogicException("Column \"$name\" already added");
        }
        $this->columns[$name] = $column;

        return $name;
    }

    /**
     * Define QBE input.
     */
    public function addQBE(QBEMeta $qbe): void
    {
        if (array_key_exists($qbe->getName(), $this->qbeMetas)) {
            throw new LogicException("QBE '{$qbe->getName()}' already defined");
        }

        $this->qbeMetas[$qbe->getName()] = $qbe;
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

    /**
     * Return true if current build phase for QBE metas.
     */
    public function isBuildForQBE(): bool
    {
        return $this->get(self::ATTR_BUILD_FOR_QBE, false);
    }

    /**
     * @return array<string, QBEMeta>
     */
    public function getQBEs(): array
    {
        return $this->qbeMetas;
    }
}
