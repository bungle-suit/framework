<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

use Bungle\Framework\Traits\Attributes;
use Bungle\Framework\Traits\HasAttributesInterface;

/**
 * Context object passed to Inquiry step functions.
 */
class StepContext implements HasAttributesInterface
{
    use Attributes;

    public Query $query;
    public QueryParams $params;
    private bool $buildForCount;

    public function __construct(bool $buildForCount, QueryParams $params)
    {
        $this->query = $q = new Query();
        $q->offset = 0;
        $q->count = -1;
        $q->docClass = $params->docClass;
        $q->fields = [];
        $q->conditions = [];
        $this->params = $params;
        $this->buildForCount = $buildForCount;
    }

    /**
     * Returns true if current build process
     * is for building count query.
     */
    public function isBuildForCount(): bool
    {
        return $this->buildForCount;
    }
}
