<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Helper;

use Bungle\Framework\Helper\FileHelper;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class FileHelperTest extends MockeryTestCase
{
    /**
     * @dataProvider hashedFilename
     */
    public function testNewHashedFilename(int $level, string $exp, string $fn): void
    {
        self::assertEquals($exp, FileHelper::newHashedFilename($level, fn () => $fn));
    }

    public function hashedFilename(): array
    {
        return [
            [0, 'foo.txt', 'foo.txt'],
            [0, 'bar', 'bar'],

            // level 1
            [1, 'f/foo.txt', 'foo.txt'],

            // level 2
            [2, 'f/o/foo.txt', 'foo.txt'],
        ];
    }

    public function testNewHashedFilenameUuid(): void
    {
        self::assertNotEmpty(FileHelper::newHashedFilename());
        self::assertEquals(40, strlen(FileHelper::newHashedFilename()));
        self::assertEquals(38, strlen(FileHelper::newHashedFilename(1)));
    }
}
