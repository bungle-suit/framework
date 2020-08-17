<?php
declare(strict_types=1);

namespace Bungle\Framework\Export;

use Bungle\Framework\Export\ExcelWriter\ExcelWriter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

abstract class AbstractExcelExporter extends AbstractExporter
{
    protected function doBuild(string $fn, array $params): void
    {
        $sheet = $this->createSpreadsheet();
        $writer = new ExcelWriter($sheet);
        $this->generate($writer, $params);
        (new Xlsx($sheet))->save($fn);
    }

    protected function createSpreadsheet(): Spreadsheet
    {
        $r = new Spreadsheet();
        $r->getDefaultStyle()->getFont()->setName('宋体');
        return $r;
    }

    abstract protected function generate(ExcelWriter $writer, array $params);
}
