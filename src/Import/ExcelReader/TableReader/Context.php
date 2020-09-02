<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\TableReader;

use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Model\HasAttributes;
use Bungle\Framework\Model\HasAttributesInterface;

class Context implements HasAttributesInterface
{
    use HasAttributes;

    private ExcelReader $reader;

    public function __construct(ExcelReader $reader)
    {
        $this->reader = $reader;
    }

    public function getReader(): ExcelReader
    {
        return $this->reader;
    }
}
