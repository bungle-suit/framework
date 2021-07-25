<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Helper;

use Bungle\Framework\Helper\PathUtil;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PathUtilTest extends MockeryTestCase
{
    /**
     * @dataProvider hasExtProvider
     */
    public function testHasExt($exp, $ext, $path): void
    {
        self::assertSame($exp, PathUtil::hasExt($ext, $path));
    }

    public function hasExtProvider()
    {
        return [
            'not matched' => [false, 'txt', 'foo.jpg'],
            'not matched on file part' => [false, 'txt', 'txt'],
            'not matched on file no ext' => [false, 'txt', 'foo'],
            'matched' => [true, 'txt', 'foo.txt'],
            'matched ignore case' => [true, 'txt', 'foo.tXt'],
        ];
    }
}
