<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

class QueryFactory
{
    public const SERVICE_TAG = 'bungle.query';

    /** @var Query[] */
    private array $queries = [];

    public function getQuery(string $queryClass): Query
    {
        return $this->queries[$queryClass];
    }

    public function addQuery(Query $query): void
    {
        $this->queries[$query::class] = $query;
    }
}
