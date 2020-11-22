<?php

declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\TableReader;

use Bungle\Framework\Import\ExcelReader\ExcelLocation;

class TableReadRowError
{
    private ExcelLocation $loc;
    private string $error;

    public function __construct(ExcelLocation $loc, string $error)
    {
        $this->loc = $loc;
        $this->error = $error;
    }

    public function getLoc(): ExcelLocation
    {
        return $this->loc;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function __toString(): string
    {
        return "$this->loc: $this->error";
    }
}
