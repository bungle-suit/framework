<?php
declare(strict_types=1);

namespace Bungle\Framework\Export;

use ArrayIterator;
use Bungle\Framework\Ent\BasalInfoService;
use Bungle\Framework\Export\ExcelWriter\ExcelColumn;
use Bungle\Framework\Export\ExcelWriter\ExcelWriter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Traversable;

/**
 * Simple table exporter, export a single 2-d table with a title.
 *
 * Excel files saved as /tmp/sidekick_excel_xxx files, use a cron to
 * delete these files periodically.
 */
abstract class AbstractSingleTableExporter extends AbstractExcelExporter
{
    private string $title;

    /** @required  */
    public BasalInfoService $basal;

    /**
     * @param string  $title  title will be filename prefix, workSheet name,
     *                        first row title by default, unless override corresponding method
     */
    public function __construct(string $title)
    {
        $this->title = $title;
    }

    protected function generate(ExcelWriter $writer, array $params)
    {
        $cols = iterator_to_array($this->createColumns(), false);
        $writer->writeTitle($this->buildTitle(), count($cols));
        $writer->writeTable($cols, $this->query($params));
        foreach (range(1, count($cols)) as $colIdx) {
            $writer->getSheet()->getColumnDimensionByColumn($colIdx)->setAutoSize(true);
        }
    }

    protected function createSpreadsheet(): Spreadsheet
    {
        $r = parent::createSpreadsheet();
        $r->getActiveSheet()->setTitle($this->buildTitle());
        return $r;
    }

    /**
     * Sub class return iterable of row object/array,
     * use doctrine query such as.
     *
     * @param mixed[] $params
     *
     * @return iterable<object|mixed[]>
     */
    abstract protected function query(array $params): iterable;

    /**
     * Return iterable of ExcelColumn.
     *
     * @return Traversable<ExcelColumn>
     */
    abstract protected function createColumns(): Traversable;

    /**
     * @inheritDoc
     */
    public function buildFilename(array $params): string
    {
        $now = $this->basal->now();

        return "{$this->title}-{$now->format('Y-m-d-His')}.xlsx";
    }

    protected function buildWorkSheetName(): string
    {
        return $this->title;
    }

    protected function buildTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    protected function buildParamParser(): Traversable
    {
        return new ArrayIterator();
    }
}
