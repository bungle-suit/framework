<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

class PagedData
{
    /**
     * Total record count in the database, use PHP_INT_MAX if the count not available.
     */
    private int $count;

    /**
     * @phpstan-var array<string, mixed[]>
     */
    private array $data;

    /**
     * @param array<string, mixed[]>
     */
    public function __construct(array $data, int $count = PHP_INT_MAX)
    {
        $this->count = $count;
        $this->data = $data;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @return array<string, mixed[]>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
