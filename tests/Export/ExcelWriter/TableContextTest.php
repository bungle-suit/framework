<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Export\ExcelWriter;

use Bungle\Framework\Export\ExcelWriter\ExcelColumn;
use Bungle\Framework\Export\ExcelWriter\ExcelWriter;
use Bungle\Framework\Export\ExcelWriter\TableContext;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class TableContextTest extends MockeryTestCase
{
    private TableContext $context;
    private ExcelColumn $col1;
    private ExcelColumn $col2;
    private ExcelColumn $col3;

    protected function setUp(): void
    {
        parent::setUp();

        $spread = new Spreadsheet();
        $writer = new ExcelWriter($spread);
        $cols = [
            $this->col1 = new ExcelColumn('foo', '[id]'),
            $this->col2 = (new ExcelColumn('bar', '[name]'))->setColSpan(3),
            $this->col3 = new ExcelColumn('foobar', '[addr]'),
        ];
        $this->context = new TableContext($writer, $cols, 2, 5);
    }

    public function testBasicProperties(): void
    {
        self::assertEquals(5, $this->context->getStartRow());
        self::assertEquals(6, $this->context->getStartDataRow());
        self::assertEquals(2, $this->context->getStartCol());
    }

    public function testGetColumnIndex(): void
    {
        self::assertEquals(2, $this->context->getColumnIndex($this->col1));
        self::assertEquals(3, $this->context->getColumnIndex($this->col2));
        self::assertEquals(6, $this->context->getColumnIndex($this->col3));
    }

    public function testGetColumnName(): void
    {
        self::assertEquals('B', $this->context->getColumnName($this->col1));
        self::assertEquals('C', $this->context->getColumnName($this->col2));
        self::assertEquals('F', $this->context->getColumnName($this->col3));
    }

    public function testGetValue(): void
    {
        $row = [1, 2, null, null, 3];
        self::assertEquals(1, $this->context->getValue($row, $this->col1));
        self::assertEquals(2, $this->context->getValue($row, $this->col2));
        self::assertEquals(3, $this->context->getValue($row, $this->col3));
    }

    public function testNewValueGetter(): void
    {
        $getter = $this->context->newValueGetter($this->col3);
        $row = [1, 2, null, null, 3];
        self::assertEquals(3, $getter($row));
    }
}
