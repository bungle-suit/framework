<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\LabelledReader;

use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Import\ExcelReader\SectionContentReaderInterface;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @phpstan-template T
 * Section content reader that value after the specific label.
 */
class LabelledReader implements SectionContentReaderInterface
{
    /** @phpstan-var T */
    private object $obj;
    private int $maxValuesPerRow;
    /** @var LabelledValueInterface[] */
    private array $values;
    /** @phpstan-var Context<T> */
    private Context $context;
    private int $startColIdx;
    private PropertyAccessor $propertyAccessor;

    /**
     * @phpstan-param T $obj
     * @param int $maxValuesPerRow max labelled values per row.
     * @param object $obj Object that parsed value will assign to.
     */
    public function __construct(object $obj, int $maxValuesPerRow, string $startCol = 'A')
    {
        $this->obj = $obj;
        $this->startColIdx = Coordinate::columnIndexFromString($startCol);
        $this->maxValuesPerRow = $maxValuesPerRow;
        $this->propertyAccessor = new PropertyAccessor();
    }

    /**
     * @phpstan-param callable(mixed, Context<T>): mixed $converter
     */
    public function defineValue(LabelledValueInterface $labelledValue): void
    {
        $this->values[] = $labelledValue;
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
            $colIdx += self::getCellWidth($reader, $colIdx);
            foreach ($this->values as $value) {
                if ($value->labelMatches($lbl, $context)) {
                    $v = $value->read($v, $context);
                    $this->propertyAccessor->setValue($this->obj, $value->getPath(), $v);
                    break;
                }
            }
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
