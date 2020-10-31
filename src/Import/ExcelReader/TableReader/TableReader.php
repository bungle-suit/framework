<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\TableReader;

use Bungle\Framework\FP;
use Bungle\Framework\Import\ExcelReader\ExcelReader;
use Bungle\Framework\Import\ExcelReader\SectionContentReaderInterface;
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
     * @phpstan-var ColumnInterface<T>[]
     */
    private array $cols;
    private $appendItem;
    private bool $firstRow = true;
    /** @var array<int, int> */
    private array $colIdxes; // column excel column index by column array index
    private $startColIdx;
    private Context $context;
    private PropertyAccessor $propertyAccessor;

    /**
     * @phpstan-param ColumnInterface<T>[] $cols
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
        $this->onRowComplete = fn () => null;
    }

    public function onSectionStart(ExcelReader $reader): void
    {
        $this->context = new Context($reader);
        $this->firstRow = true;

        $arrLabels = array_map(fn (ColumnInterface $col) => $col->getTitle(), $this->cols);
        $sheet = $reader->getSheet();
        $cols = Coordinate::columnIndexFromString($sheet->getHighestColumn($reader->getRow()));
        $this->colIdxes = [];
        for ($i = $this->startColIdx; $i <= $cols; $i ++) {
            $lbl = $reader->getCellValueByColumn($i);
            if (($idx = array_search($lbl, $arrLabels)) !== false) {
                $this->colIdxes[$idx] = $i;
            }
        }

        foreach ($arrLabels as $i => $lbl) {
            if (!isset($this->colIdxes[$i])) {
                throw new RuntimeException("工作表\"{$sheet->getTitle()}\"第{$reader->getRow()}行没有列\"$lbl\"");
            }
        }
    }

    public function readRow(ExcelReader $reader): void
    {
        if ($this->firstRow) {
            $this->firstRow = false;
            return;
        }

        $item = ($this->createItem)();
        foreach ($this->colIdxes as $i => $colIdx) {
            $col = $this->cols[$i];
            $v = $reader->getCellValueByColumn($colIdx);
            $v = $col->read($v, $this->context);
            $this->propertyAccessor->setValue($item, $col->getPath(), $v);
        }
        ($this->onRowComplete)($item, $this->context);
        ($this->appendItem)($item);
    }

    public function onSectionEnd(ExcelReader $reader): void
    {
    }

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

    public function setOnRowComplete(callable $onRowComplete): self
    {
        $this->onRowComplete = $onRowComplete;

        return $this;
    }
}
