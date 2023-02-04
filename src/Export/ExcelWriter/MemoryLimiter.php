<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter;

use RuntimeException;

/**
 * Parse php.inp memory_limit value, if current usage near limit, throw
 * RuntimeException
 */
class MemoryLimiter
{
    private int $memoryLimit;

    public function __construct(?int $memoryLimit = null)
    {
        $this->memoryLimit = $memoryLimit ?? self::parseMemoryLimit();
    }

    public function check(): void
    {
        if (memory_get_usage(true) > $this->memoryLimit * 0.75) {
            throw new RuntimeException('导出数据太多，内存不足');
        }
    }

    public static function parseMemoryLimit(): int
    {
        // parse php.ini memory_limit
        $memory_limit = ini_get('memory_limit');
        if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
            if ($matches[2] == 'G') {
                $memory_limit = $matches[1] * 1024 * 1024 * 1024; // nnnG -> nnn GB
            } elseif ($matches[2] == 'M') {
                $memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
            } elseif ($matches[2] == 'K') {
                $memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
            }
        }

        return intval($memory_limit);
    }
}
