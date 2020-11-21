<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Export\ExcelWriter;

use Bungle\Framework\Export\ExcelWriter\ExcelColumn;
use DateTime;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ExcelColumnTest extends MockeryTestCase
{
    public function testConstruct(): void
    {
        $col = new ExcelColumn('foo', '[0]');
        self::assertEquals('foo', $col->getHeader());
        self::assertEquals('foo', $col->getValueConverter()('foo', 1, ['row']));
        self::assertEquals('[0]', $col->getPropertyPath());
    }

    public function testCreateDate(): void
    {
        $col = ExcelColumn::createDate('foo', 'a.b');
        $f = $col->getValueConverter();
        self::assertIsFloat($f(new DateTime(), 1, ['row']));
        self::assertNull($f(null, 2, ['row']));
    }

    public function testFormula(): void
    {
        $col = new ExcelColumn('foo', '');
        self::assertFalse($col->formulaEnabled());

        $gen = fn(int $row) => '=expr';
        self::assertSame($col, $col->setFormula($gen));
        self::assertTrue($col->formulaEnabled());
        self::assertSame($gen, $col->getFormula());
    }
}
