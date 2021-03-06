<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\LabelledReader;

use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Model\HasAttributes;
use Bungle\Framework\Model\HasAttributesInterface;

/**
 * @template T
 */
class Context implements HasAttributesInterface
{
    use HasAttributes;

    /** @phpstan-var T */
    private $obj;
    private ExcelReader $reader;

    /**
     * @phpstan-param T $obj
     */
    public function __construct(ExcelReader $reader, $obj)
    {
        $this->obj = $obj;
        $this->reader = $reader;
    }

    /**
     * @phpstan-return T
     * @return mixed
     */
    public function getObject()
    {
        return $this->obj;
    }

    public function getReader(): ExcelReader
    {
        return $this->reader;
    }
}
