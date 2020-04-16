<?php

declare(strict_types=1);

namespace Bungle\Framework\Ent\Inquiry;

/**
 * Abstract implement QueryBuilderInterface.
 *
 * If QueryBuilderInterface add more methods,
 * AbstractQueryBuilder will provide default implementation,
 * create sub class of AbstractQueryBuilder won't break
 * if QueryBuilderInterface add more features.
 */
abstract class AbstractQueryBuilder implements QueryBuilderInterface
{
    public function steps(): array
    {
        return [];
    }
}
