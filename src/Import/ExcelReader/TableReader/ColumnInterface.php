<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\TableReader;

/**
 * Column of for TableReader.
 */
interface ColumnInterface
{
    /**
     * Path to the value of the row object.
     */
    public function getPath(): string;

    /**
     * Title of the column
     */
    public function getTitle(): string;

    public function getHeaderCellMatcher(): ColumnHeaderCellMatcherInterface;

    /**
     * @phpstan-param Context $context
     * @param mixed $val
     * @return mixed Read and convert the cell value.
     */
    public function read($val, Context $context);

    /**
     * It is okay if excel table no this column
     */
    public function isOptional(): bool;
}
