<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

class Query
{
    public int $offset;
    // -1 means do not set limit on query result dataset.
    public int $count;
    public string $docClass;
    // empty means select the whole object.
    public array $fields;

    /**
     * @var ConditionInterface[]
     */
    public array $conditions;
}
