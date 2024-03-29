<?php

declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\LabelledReader;

use Bungle\Framework\FP;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

/**
 * @template T
 */
class LabelledValue
{
    private string $path;
    /** @var string[] */
    private array $labels;

    /** @var callable(mixed, Context<T>): mixed */
    private $converter;

    public const MODE_READ = 0;
    public const MODE_WRITE = 1;

    private int $mode = self::MODE_READ;
    /** @var callable(mixed, Context<T>, Cell): mixed */
    private $writeConverter;

    private string $cellFormat = '';

    private $onLabelCell;

    public function __construct(string $path, string ...$labels)
    {
        $this->path = $path;
        $this->labels = $labels;
        $this->converter = [FP::class, 'identity'];
        $this->onLabelCell = FP::null();
    }

    public function labelMatches(string $label): bool
    {
        return in_array($label, $this->labels);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param mixed $val
     * @phpstan-param Context<T> $context
     * @return mixed
     */
    public function read($val, Context $context)
    {
        return ($this->converter)($val, $context);
    }

    /**
     * @phpstan-param Context<T> $context
     */
    public function onSectionEnd(Context $context): void
    {
    }

    public function getConverter(): callable
    {
        return $this->converter;
    }

    /**
     * @phpstan-return self<T>
     */
    public function setConverter(callable $converter): self
    {
        $this->converter = $converter;

        return $this;
    }

    /**
     * Read or write mode.
     */
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * Enable write mode.
     *
     * Note: currently no read/write mode, after setWriteMode(), current value
     * will act as write only labelled value.
     *
     * @param callable(mixed, Context<T>, Cell): mixed $fWriteConverter
     * @phpstan-return self<T>
     */
    public function setWriteMode(callable $fWriteConverter = null): self
    {
        $this->mode = self::MODE_WRITE;
        $this->writeConverter = $fWriteConverter ?? [FP::class, 'identity'];

        return $this;
    }

    /**
     * Write converter converts object value to excel value, if returns null,
     * skip set cell value, return empty string, if want set the cell to empty.
     *
     * @return callable(mixed, Context<T>, Cell): mixed
     */
    public function getWriteConverter(): callable
    {
        return $this->writeConverter;
    }

    /**
     * Set cell format for write mode, empty string means no explicit format.
     */
    public function getCellFormat(): string
    {
        return $this->cellFormat;
    }

    /**
     * @phpstan-return self<T>
     */
    public function setCellFormat(string $cellFormat): self
    {
        $this->cellFormat = $cellFormat;

        return $this;
    }

    /**
     * @return callable(Cell): void
     */
    public function getOnLabelCell(): callable
    {
        return $this->onLabelCell;
    }

    /**
     * @param callable(Cell): void $f
     */
    public function setOnLabelCell(callable $f): self
    {
        $this->onLabelCell = $f;

        return $this;
    }

    /**
     * @var callable(Cell): void
     */
    protected $cellWriter;

    /**
     * Register a callable to modify cell, worked in write mode.
     * @return ?(callable(Cell): void)
     */
    public function getCellWriter(): ?callable
    {
        return $this->cellWriter ?? null;
    }

    /**
     * @param callable(Cell): void $v
     */
    public function setCellWriter(callable $v): self
    {
        $this->cellWriter = $v;

        return $this;
    }
}
