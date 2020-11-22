<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader\TableReader;

use Bungle\Framework\Import\ExcelReader\ExcelLocation;
use Bungle\Framework\Import\ExcelReader\TableReader\TableReadException;
use Bungle\Framework\Import\ExcelReader\TableReader\TableReadRowError;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

class TableReadExceptionTest extends MockeryTestCase
{
    public function testMessage(): void
    {
        $err1 = new TableReadRowError(new ExcelLocation('foo', 3), new RuntimeException('err1'));
        $err2 = new TableReadRowError(new ExcelLocation('foo', 4), new RuntimeException('err2'));
        $ex = new TableReadException([$err1, $err2]);
        self::assertEquals(
            <<<Err
            导入Excel出现错误:

            工作表"foo"第3行: err1
            工作表"foo"第4行: err2
            Err
            ,
            $ex->getMessage()
        );
    }
}
