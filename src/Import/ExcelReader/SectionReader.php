<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader;

use Bungle\Framework\FP;

/**
 * Support read a section from ExcelReader.
 *
 * Composed with two parts:
 *
 * 1. SectionBoundaryInterface
 * 2. ContentReaderInterface
 */
class SectionReader
{
    private SectionBoundaryInterface $boundary;
    private SectionContentReaderInterface $contentReader;
    private string $name; // section name;
    /** @var callable(ExcelReader): bool */
    private $isEmptyRow;

    public function __construct(
        string $name,
        SectionBoundaryInterface $boundary,
        SectionContentReaderInterface $contentReader
    ) {
        $this->isEmptyRow = FP::f();
        $this->name = $name;
        $this->boundary = $boundary;
        $this->contentReader = $contentReader;
    }

    public function getBoundary(): SectionBoundaryInterface
    {
        return $this->boundary;
    }

    public function getContentReader(): SectionContentReaderInterface
    {
        return $this->contentReader;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return callable(ExcelReader): bool
     */
    public function getIsEmptyRow(): callable
    {
        return $this->isEmptyRow;
    }

    /**
     * Setup isEmptyRow function, returns true if current row is empty.
     * Default implementation always return false, no empty row support.
     *
     * @param callable(ExcelReader): bool $isEmptyRow
     */
    public function setIsEmptyRow(callable $isEmptyRow): self
    {
        $this->isEmptyRow = $isEmptyRow;
        return $this;
    }
}
