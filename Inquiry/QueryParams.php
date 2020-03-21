<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

/**
 * Parameters of current query, such as QBE values.
 * Must support serializable, maybe saved in session.
 */
class QueryParams
{
    public int $pageNo; // Page no start from zero.

    public string $docClass;
    // Object that each field is simple literal value, or QBEValueInterface
    public $qbe;

    public function __construct(string $docClass, int $pageNo, $qbe)
    {
        $this->docClass = $docClass;
        $this->pageNo = $pageNo;
        $this->qbe = $qbe;
    }
}
