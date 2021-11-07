<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Helper;

use Bungle\Framework\Helper\DebugHelper;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.Superglobals)
 */
final class DebugHelperTest extends TestCase
{
    public function testIsDebug(): void
    {
        self::assertTrue(DebugHelper::isDebug());

        $_SERVER['APP_DEBUG'] = '0';
        self::assertTrue(DebugHelper::isDebug()); // isDebug caches result.
        self::assertFalse(DebugHelper::resolveIsDebug());

        $_SERVER['APP_DEBUG'] = '1';
        self::assertTrue(DebugHelper::resolveIsDebug());
    }

    public function testIsUnitTest(): void
    {
        self::assertTrue(DebugHelper::isUnitTest());
    }
}
