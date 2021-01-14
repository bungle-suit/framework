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
}
