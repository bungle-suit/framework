<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader\TableReader;

use Bungle\Framework\Import\ExcelReader\TableReader\Column;
use Bungle\Framework\Import\ExcelReader\TableReader\Context;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ColumnTest extends MockeryTestCase
{
    public function testRead(): void
    {
        $ctx = Mockery::mock(Context::class);
        // case 1 without converter
        $col = new Column('path', 'lbl');
        self::assertEquals('foo', $col->read('foo', $ctx));

        // case 2 with converter
        $col->setConverter(fn ($val, Context $ctx) => intval($val));
        self::assertEquals(123, $col->read('123', $ctx));
    }
}
