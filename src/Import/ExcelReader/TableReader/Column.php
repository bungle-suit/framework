<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\TableReader;

use Bungle\Framework\FP;

class Column implements ColumnInterface
{
    private string $path;
    private string $title;
    /** @var callable(mixed, Context): mixed */
    private $converter;
    private ColumnHeaderCellMatcherInterface $headerCellMatcher;

    public function __construct(string $path, string $title, ColumnHeaderCellMatcherInterface $headerCellMatcher = null)
    {
        $this->path = $path;
        $this->title = $title;
        $this->converter = [FP::class, 'identity'];
        $this->headerCellMatcher = $headerCellMatcher ?? new TextColumnHeaderCellMatcher($title);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function read($val, Context $context)
    {
        return ($this->converter)($val, $context);
    }

    /**
     * @return callable(mixed, Context): mixed
     */
    public function getConverter(): callable
    {
        return $this->converter;
    }

    /**
     * @phpstan-param callable(mixed, Context): mixed
     */
    public function setConverter(callable $converter): self
    {
        $this->converter = $converter;

        return $this;
    }

    public function getHeaderCellMatcher(): ColumnHeaderCellMatcherInterface
    {
        return $this->headerCellMatcher;
    }
}
