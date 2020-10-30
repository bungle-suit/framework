<?php
declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter;

use Bungle\Framework\FP;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

final class ExcelColumn
{
    private string $propertyPath;
    /** @var callable(mixed, int, mixed): mixed */
    private $valueConverter;
    private string $header;
    private ?string $cellFormat = null;
    private bool $enableSum = false;
    /**
     * @var callable $formula
     * @phpstan-var callable(int): string $formula
     */
    private $formula; // 公式
    private int $colSpan;
    private bool $mergeCells;

    /**
     * @param string $header header text
     * @param string $propertyPath will return the row data if path is empty, use converter to
     *     shape cell data.
     * @param null|callable(mixed, int, mixed):mixed $valueConverter function to convert value
     *     before saving to cell, use identity converter if null.
     *     First argument is the value or row if $propertyPath is empty, 2nd argument is
     *     row index from zero, 3rd argument is the row object.
     */
    public function __construct(
        string $header,
        string $propertyPath,
        ?callable $valueConverter = null
    ) {
        $this->propertyPath = $propertyPath;
        $this->valueConverter = $valueConverter ?? [FP::class, 'identity'];
        $this->header = $header;
    }

    public function getPropertyPath(): string
    {
        return $this->propertyPath;
    }

    /**
     * @return callable(mixed, int, mixed): mixed
     */
    public function getValueConverter(): callable
    {
        return $this->valueConverter;
    }

    public function getHeader(): string
    {
        return $this->header;
    }

    /**
     * If not null, it will apply to data cells of this column.
     */
    public function getCellFormat(): ?string
    {
        return $this->cellFormat;
    }

    public function setCellFormat(?string $cellFormat): self
    {
        $this->cellFormat = $cellFormat;

        return $this;
    }

    public static function createDate(string $title, string $propertyPath): self
    {
        $f = fn($v) => (null === $v) ? null : Date::PHPToExcel($v);

        return (new self($title, $propertyPath, $f))
            ->setCellFormat(NumberFormat::FORMAT_DATE_YYYYMMDD);
    }

    public static function createNo(string $title = '序号'): self
    {
        return new self($title, '', fn($v, int $rowIdx) => $rowIdx + 1);
    }

    public function isEnableSum(): bool
    {
        return $this->enableSum;
    }

    public function enableSum(bool $enableSum = true): self
    {
        $this->enableSum = $enableSum;

        return $this;
    }

    /**
     * Returns excel formula, write the formula using the function result.
     *
     * @phpstan-return callable(int): string
     */
    public function getFormula(): callable
    {
        return $this->formula;
    }

    /**
     * The argument of callable is current row index.
     *
     * @phpstan-param callable(int): string $formula
     */
    public function setFormula(callable $formula): self
    {
        // Set formula cells data to null.
        // Normally the propertyPath of formula column is empty, ExcelWriter
        // will use the whole row data as cell value, it will fail if
        // valueConverter failed convert to type that PHPSpreadsheet accept.
        $this->valueConverter = FP::constant(null);
        $this->formula = $formula;

        return $this;
    }

    public function formulaEnabled(): bool
    {
        return isset($this->formula);
    }

    public function getColSpan(): int
    {
        return $this->colSpan ?? 1;
    }

    public function setColSpan(int $colSpan): self
    {
        $this->colSpan = $colSpan;

        return $this;
    }

    public function isMergeCells(): bool
    {
        return $this->mergeCells ?? false;
    }

    public function setMergeCells(bool $mergeCells): self
    {
        $this->mergeCells = $mergeCells;
        return $this;
    }
}
