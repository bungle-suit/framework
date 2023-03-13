<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Export;

use Bungle\Framework\Export\FS;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class FSTest extends MockeryTestCase
{
    public function testWriteToString(): void
    {
        [$f, $capture] = FS::writeToString();
        self::assertNotFalse(fwrite($f, 'abc'));
        self::assertEquals('abc', $capture());
        self::assertFalse(is_resource($f));
    }

    public function testReadIZip(): void
    {
        $fs = new FS();

        $path = __DIR__.'/izip-test.zip';
        $exp = file_get_contents('zip://'.$path.'#20230310-银丝垂柳-无标-衣柜-1单-0315/8121070/81210701004.mpr');

        self::assertEquals($exp, $fs->readFile('izip://'.$path.'#5'));
        self::assertEquals($exp, $fs->readFile('izip://'.$path.'#5#20230310-银丝垂柳-无标-衣柜-1单-0315/8121070/81210701004.mpr'));
    }
}
