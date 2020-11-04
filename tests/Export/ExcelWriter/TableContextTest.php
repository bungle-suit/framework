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
    private ExcelWriter $writer;

    protected function setUp(): void
    {
        parent::setUp();

        $spread = new Spreadsheet();
        $this->writer = new ExcelWriter($spread);
        $cols = [
            $this->col1 = new ExcelColumn('foo', '[id]'),
            $this->col2 = (new ExcelColumn('bar', '[name]'))->setColSpan(3),
            $this->col3 = new ExcelColumn('foobar', '[addr]'),
        ];
        $this->context = new TableContext($this->writer, $cols, 2, 5);
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

    /**
     * @param string[] $exp
     * @param ExcelColumn[] $cols
     * @dataProvider getColumnEndNameProvider
     */
    public function testGetColumnEndName(array $exp, array $cols): void
    {
        $context = new TableContext($this->writer, $cols, 2, 5);
        $act = array_map(fn(ExcelColumn $col) => $context->getColumnEndName($col), $cols);
        self::assertEquals($exp, $act);
    }

    /**
     * @return mixed[]
     */
    public function getColumnEndNameProvider(): array
    {
        $col1 = new ExcelColumn('foo', '[id]');
        $col2 = (new ExcelColumn('bar', '[name]'))->setColSpan(3);
        $col3 = new ExcelColumn('foobar', '[addr]');
        $col4 = (new ExcelColumn('foobar', '[addr]'))->setColSpan(2);

        return [
            [['B', 'E', 'F'], [$col1, $col2, $col3]],
            [['B', 'E', 'G'], [$col1, $col2, $col4]],
        ];
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
