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
    private bool $titleBuilt = false;

    /** @required */
    public BasalInfoService $basal;

    /**
     * @param string $title title will be filename prefix, workSheet name,
     *                        first row title by default, unless override corresponding method
     */
    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    protected function doBuild(string $fn, array $params): void
    {
        $this->doGetTitle($params);
        parent::doBuild($fn, $params);
    }

    protected function generate(ExcelWriter $writer, array $params): void
    {
        $cols = iterator_to_array($this->createColumns(), false);
        $this->writeTitle($writer, $params, count($cols));
        $writer->writeTable($cols, $this->query($params), 'A', $this->getTableOptions());
    }

    protected function writeTitle(ExcelWriter $writer, array $params, int $colCount): void
    {
        $writer->writeTitle($this->doGetTitle($params), $colCount);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getTableOptions(): array
    {
        return [];
    }

    /** @inheritDoc */
    protected function createSpreadsheet(array $params): Spreadsheet
    {
        $r = parent::createSpreadsheet($params);
        $r->getActiveSheet()->setTitle($this->doGetTitle($params));

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

        return "{$this->doGetTitle($params)}-{$now->format('Y-m-d-His')}.xlsx";
    }

    /**
     * @param mixed[] $params
     */
    protected function doGetTitle(array $params): string
    {
        if (!$this->titleBuilt) {
            $this->titleBuilt = true;
            $this->title = $this->buildTitle($params);
        }

        return $this->title;
    }

    /**
     * @param mixed[] $params
     * @noinspection PhpUnusedParameterInspection
     */
    protected function buildTitle(array $params): string
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
