<?php

declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\TableReader;

use Bungle\Framework\FP;
use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Import\ExcelReader\SectionContentReaderInterface;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @phpstan-template T
 */
class TableReader implements SectionContentReaderInterface
{
    /** @var callable(): T */
    private $createItem;
    /** @var callable(T, Context): void */
    private $onRowComplete;
    /**
     * @var ColumnInterface[]
     */
    private array $cols;
    /** @var callable(T): void */
    private $appendItem;
    private bool $firstRow = true;
    /** @var array<int, int> */
    private array $colIdxes; // column excel column index by column array index
    private int $startColIdx;
    private Context $context;
    private PropertyAccessor $propertyAccessor;
    /** @var TableReadRowError[] */
    private array $rowErrors;

    /**
     * @param ColumnInterface[] $cols
     * @phpstan-param callable(T): void $appendItem
     */
    public function __construct(array $cols, callable $appendItem, string $startCol = 'A')
    {
        $this->cols = $cols;
        $this->appendItem = $appendItem;
        $this->startColIdx = Coordinate::columnIndexFromString($startCol);
        $this->createItem = FP::constant([]);
        $this->propertyAccessor = new PropertyAccessor();
        $this->onRowComplete = function (): void {
        };
    }

    public function onSectionStart(ExcelReader $reader): void
    {
        $this->rowErrors = [];
        $this->context = new Context($reader);
        $this->firstRow = true;

        $arrLabels = array_map(fn(ColumnInterface $col) => $col->getTitle(), $this->cols);
        $sheet = $reader->getSheet();
        $cols = Coordinate::columnIndexFromString($sheet->getHighestColumn("{$reader->getRow()}"));
        $this->colIdxes = [];
        for ($i = $this->startColIdx; $i <= $cols; $i++) {
            /** @var Cell $cell */
            $cell = $reader->getSheet()->getCellByColumnAndRow($i, $reader->getRow());
            $col = FP::firstOrNull(
                fn(ColumnInterface $c): bool => $c->getHeaderCellMatcher()->matches($cell),
                $this->cols
            );
            if ($col === null) {
                continue;
            }
            $idx = array_search($col, $this->cols, true);
            assert(is_int($idx));
            $this->colIdxes[$idx] = $i;
        }

        foreach ($arrLabels as $i => $lbl) {
            if (!$this->cols[$i]->isOptional() && !isset($this->colIdxes[$i])) {
                throw new RuntimeException(
                    "工作表\"{$sheet->getTitle()}\"第{$reader->getRow()}行没有列\"$lbl\""
                );
            }
        }
    }

    public function readRow(ExcelReader $reader): void
    {
        if ($this->firstRow) {
            $this->firstRow = false;

            return;
        }

        $hasError = false;
        $item = ($this->createItem)();
        foreach ($this->colIdxes as $i => $colIdx) {
            try {
                $col = $this->cols[$i];
                $v = $reader->getCellValueByColumn($colIdx);
                $v = $col->read($v, $this->context);
                $this->propertyAccessor->setValue($item, $col->getPath(), $v);
            } catch (RuntimeException $e) {
                $hasError = true;
                $this->rowErrors[] = new TableReadRowError(
                    $reader->getLocation(Coordinate::stringFromColumnIndex($colIdx)), $e
                );
            }
        }
        if (!$hasError) {
            ($this->onRowComplete)($item, $this->context);
            ($this->appendItem)($item);
        }
    }

    /**
     * @throws TableReadException if any exception during reading rows.
     */
    public function onSectionEnd(ExcelReader $reader): void
    {
        if ($this->rowErrors) {
            throw new TableReadException($this->rowErrors);
        }
    }

    /**
     * @phpstan-param callable(): T $createItem
     * @phpstan-return self<T>
     */
    public function setCreateItem(callable $createItem): self
    {
        $this->createItem = $createItem;

        return $this;
    }

    /**
     * Callback to create the item object.
     * @phpstan-return callable(): T
     */
    public function getCreateItem(): callable
    {
        return $this->createItem;
    }

    /**
     * Callback called when finished the row.
     * @phpstan-return callable(T, Context): void
     */
    public function getOnRowComplete(): callable
    {
        return $this->onRowComplete;
    }

    /**
     * @phpstan-param callable(T, Context): void $onRowComplete
     * @phpstan-return self<T>
     */
    public function setOnRowComplete(callable $onRowComplete): self
    {
        $this->onRowComplete = $onRowComplete;

        return $this;
    }
}
