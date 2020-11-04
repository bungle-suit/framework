<?php
declare(strict_types=1);

namespace Bungle\Framework\Export;

use Bungle\Framework\Export\ExcelWriter\ExcelWriter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

abstract class AbstractExcelExporter extends AbstractExporter
{
    /**
     * @inheritDoc
     */
    protected function doBuild(string $fn, array $params): void
    {
        $sheet = $this->createSpreadsheet($params);
        $writer = new ExcelWriter($sheet);
        $this->generate($writer, $params);
        (new Xlsx($sheet))->save($fn);
    }

    /**
     * @param mixed[] $params
     * @noinspection PhpUnusedParameterInspection
     */
    protected function createSpreadsheet(array $params): Spreadsheet
    {
        $r = new Spreadsheet();
        $r->getDefaultStyle()->getFont()->setName('宋体');
        $r->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        return $r;
    }

    /**
     * @param mixed[] $params
     */
    abstract protected function generate(ExcelWriter $writer, array $params): void;
}
