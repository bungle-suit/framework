<?php

declare(strict_types=1);

namespace Bungle\Framework\Ent\Inquiry;

use Bungle\Framework\Model\HasAttributes;
use Bungle\Framework\Model\HasAttributesInterface;
use Doctrine\ODM\MongoDB\Query\Builder;

/**
 * Context object passed to Inquiry step functions.
 */
class StepContext implements HasAttributesInterface
{
    use HasAttributes;

    private bool $buildForCount;

    /**
     * @var Builder
     */
    private Builder $builder;
    /**
     * @var QueryParams
     */
    private QueryParams $params;

    public function __construct(bool $buildForCount, QueryParams $params, Builder $builder)
    {
        $this->buildForCount = $buildForCount;
        $this->builder = $builder;
        $this->params = $params;
    }

    /**
     * Returns true if current build process
     * is for building count query.
     */
    public function isBuildForCount(): bool
    {
        return $this->buildForCount;
    }

    public function getBuilder(): Builder
    {
        return $this->builder;
    }

    public function getParams(): QueryParams
    {
        return $this->params;
    }
}
