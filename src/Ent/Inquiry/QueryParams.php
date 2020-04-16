<?php

declare(strict_types=1);

namespace Bungle\Framework\Ent\Inquiry;

/**
 * Parameters of current query, such as QBE values.
 * Must support serializable, maybe saved in session.
 */
class QueryParams
{
    /**
     * @see QueryParams::$sort
     */
    public const ORDER_ASC = true;

    /**
     * @see QueryParams::$sort
     */
    public const ORDER_DESC = false;

    public int $pageNo; // Page no start from zero.

    public string $docClass;
    // Object that each field is simple literal value, or QBEValueInterface
    public $qbe;

    /**
     * Sort by field, restrictions:
     *
     * 1. Can only set one field
     * 2. Expression not allowed.
     *
     * @var null|string[]|bool[]
     *
     * Two element tuple, first is field name, 2nd is bool, use ORDER_ASC, ORDER_DESC const.
     */
    public ?array $sort = null;

    public function __construct(string $docClass, int $pageNo, $qbe)
    {
        $this->docClass = $docClass;
        $this->pageNo = $pageNo;
        $this->qbe = $qbe;
    }
}
