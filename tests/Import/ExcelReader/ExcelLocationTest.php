<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader;

use Bungle\Framework\Import\ExcelReader\ExcelLocation;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ExcelLocationTest extends MockeryTestCase
{
    public function testToString(): void
    {
        $loc = new ExcelLocation('foo', 3);
        self::assertEquals('工作表"foo"第3行', (string)$loc);
    }
}
