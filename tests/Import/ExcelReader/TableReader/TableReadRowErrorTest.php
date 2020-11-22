<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader\TableReader;

use Bungle\Framework\Import\ExcelReader\ExcelLocation;
use Bungle\Framework\Import\ExcelReader\TableReader\TableReadRowError;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery;

class TableReadRowErrorTest extends MockeryTestCase
{
    public function testToString(): void
    {
        $loc = new ExcelLocation('foo', 3);
        $err = new TableReadRowError($loc, 'blah');

        self::assertEquals('工作表"foo"第3行: blah', strval($err));
    }
}
