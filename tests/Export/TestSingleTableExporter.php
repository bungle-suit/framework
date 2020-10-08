<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Export;

use Bungle\Framework\Export\AbstractSingleTableExporter;
use Bungle\Framework\Export\ExcelWriter\ExcelColumn;
use Traversable;

class TestSingleTableExporter extends AbstractSingleTableExporter
{
    protected function query(array $qBEs): iterable
    {
        yield [1, 'foo'];
        yield [2, 'bar'];
        yield [3, 'blah'];
    }

    protected function createColumns(): Traversable
    {
        yield new ExcelColumn('ID', '[0]');
        yield new ExcelColumn('Name', '[1]');
    }

    public function buildTitle(): string
    {
        return parent::buildTitle();
    }
}
