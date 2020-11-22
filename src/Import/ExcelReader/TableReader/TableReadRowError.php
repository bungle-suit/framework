<?php

declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\TableReader;

use Bungle\Framework\Import\ExcelReader\ExcelLocation;
use RuntimeException;

class TableReadRowError
{
    private ExcelLocation $loc;
    private RuntimeException $error;

    public function __construct(ExcelLocation $loc, RuntimeException $error)
    {
        $this->loc = $loc;
        $this->error = $error;
    }

    public function getLoc(): ExcelLocation
    {
        return $this->loc;
    }

    public function getError(): RuntimeException
    {
        return $this->error;
    }

    public function __toString(): string
    {
        return "$this->loc: {$this->error->getMessage()}";
    }
}
