<?php

declare(strict_types=1);

namespace Bungle\Framework\Ent\Inquiry;

class PagedData
{
    /**
     * Total record count in DB, use PHP_INT_MAX if the count not available.
     */
    public int $count;

    public array $data;

    public function __construct(array $data, int $count = PHP_INT_MAX)
    {
        $this->count = $count;
        $this->data = $data;
    }
}
