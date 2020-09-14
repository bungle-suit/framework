<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

/**
 * Parameters of current query, such as QBE values.
 * Must support serializable, maybe saved in session.
 */
class QueryParams
{
    public const ORDER_ASC = true;
    public const ORDER_DESC = false;

    /**
     * Page no start from zero.
     */
    private int $pageNo;

    /**
     * @var object|array<string, mixed>
     */
    private $qbe;

    /**
     * @var array<array{string, boolean}>
     *
     * Two elements tuple, first is column name, 2nd is bool, use ORDER_ASC/ORDER_DESC const.
     */
    private array $sort;

    /**
     * Options pass to query steps to control their behaviors.
     * @var array<string, mixed>
     */
    private array $options;

    /**
     * @param object|array<string, mixed> $qbe
     * @param array<string, mixed> $options
     * @phpstan-param array<array{string, boolean}> $sort
     */
    public function __construct(int $pageNo, $qbe, array $sort = [], array $options = [])
    {
        $this->pageNo = $pageNo;
        $this->qbe = $qbe;
        $this->options = $options;
        $this->sort = $sort;
    }

    public function getPageNo(): int
    {
        return $this->pageNo;
    }

    public function getQbe()
    {
        return $this->qbe;
    }

    public function getSort(): array
    {
        return $this->sort;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
