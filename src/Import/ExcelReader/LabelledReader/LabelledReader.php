<?php

declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\LabelledReader;

use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Import\ExcelReader\SectionContentReaderInterface;
use LogicException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @phpstan-template T
 * Section content reader that value after the specific label.
 */
class LabelledReader implements SectionContentReaderInterface
{
    /** @phpstan-var T */
    private $obj;
    private int $maxValuesPerRow;
    /** @phpstan-var LabelledValue<T>[] */
    private array $values;
    /** @phpstan-var Context<T> */
    private Context $context;
    private int $startColIdx;
    private PropertyAccessor $propertyAccessor;

    /**
     * @phpstan-param T $obj Object that parsed value will assign to.
     * @param int $maxValuesPerRow max labelled values per row.
     */
    public function __construct($obj, int $maxValuesPerRow, string $startCol = 'A')
    {
        $this->obj = $obj;
        $this->startColIdx = Coordinate::columnIndexFromString($startCol);
        $this->maxValuesPerRow = $maxValuesPerRow;
        $this->propertyAccessor = new PropertyAccessor();
    }

    /**
     * @phpstan-param LabelledValue<T> $labelledValue
     * @phpstan-return self<T>
     */
    public function defineValue(LabelledValue $labelledValue): self
    {
        $this->values[] = $labelledValue;

        return $this;
    }

    public function onSectionStart(ExcelReader $reader): void
    {
        $this->context = new Context($reader, $this->obj);
    }

    public function readRow(ExcelReader $reader): void
    {
        $context = $this->context;
        assert($context->getReader() === $reader);

        $colIdx = $this->startColIdx;
        for ($i = 0; $i < $this->maxValuesPerRow; $i++) {
            $lbl = (string)($reader->getCellValueByColumn($colIdx));
            $colIdx += self::getCellWidth($reader, $colIdx);
            $v = $reader->getCellValueByColumn($colIdx);
            /**
             * @var LabelledValue $value
             * @phpstan-var LabelledValue<T> $value
             */
            foreach ($this->values as $value) {
                if ($value->labelMatches($lbl)) {
                    switch ($value->getMode()) {
                        case LabelledValue::MODE_READ:
                            $v = $value->read($v, $context);
                            $this->propertyAccessor->setValue($this->obj, $value->getPath(), $v);
                            break;
                        case LabelledValue::MODE_WRITE:
                            $v = $this->propertyAccessor->getValue($this->obj, $value->getPath());
                            $v = ($value->getWriteConverter())($v, $context);
                            $reader->setCellValue($colIdx, $v);
                            break;
                        default:
                            throw new LogicException(
                                'Unknown LabelledValue mode: '.$value->getMode()
                            );
                    }
                }
            }
            $colIdx += self::getCellWidth($reader, $colIdx);
        }
    }

    private static function getCellWidth(ExcelReader $reader, int $col): int
    {
        $sheet = $reader->getSheet();
        $cell = $sheet->getCellByColumnAndRow($col, $reader->getRow(), false);
        if ($cell === null) {
            return 1;
        }

        if (($range = $cell->getMergeRange()) === false) {
            return 1;
        }

        return Coordinate::rangeDimension($range)[0];
    }

    public function onSectionEnd(ExcelReader $reader): void
    {
        assert($this->context->getReader() === $reader);

        foreach ($this->values as $value) {
            $value->onSectionEnd($this->context);
        }
    }
}
