<?php

declare(strict_types=1);

namespace Bungle\Framework\Helper;

/**
 * @SuppressWarnings(PHPMD.Superglobals)
 */
final class DebugHelper
{
    /**
     * Returns true if debug enabled:.
     *
     * 1. $_SERVER['APP_DEBUG'] is '1', it is set by symfony
     * 2. If $_SERVER['APP_DEBUG'] unset, assume debug mode
     */
    public static function isDebug(): bool
    {
        static $isDebug;
        if (isset($isDebug)) {
            return $isDebug;
        }

        return $isDebug = self::resolveIsDebug();
    }

    /**
     * Low level function for isDebug(), isDebug caches resolveIsDebug() result.
     */
    public static function resolveIsDebug(): bool
    {
        if (!array_key_exists('APP_DEBUG', $_SERVER)) {
            return true;
        }

        return boolval($_SERVER['APP_DEBUG']);
    }

    /**
     * Returns true if current env is unit test.
     * To make this work, define constant in phpunit.xml:
     *
     *  ...
     *  <php>
     *      <const name="RUN_IN_UNIT_TEST" value="true"/>
     *  </php>
     * </phpunit>
     */
    public static function isUnitTest(): bool
    {
        return defined('RUN_IN_UNIT_TEST');
    }
}
