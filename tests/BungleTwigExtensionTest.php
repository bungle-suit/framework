<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Twig;

use AssertionError;
use Bungle\Framework\Twig\BungleTwigExtension;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class BungleTwigExtensionTest extends TestCase
{
    public function testFormat()
    {
        $f = self::getFilterFunc('bungle_format');
        $f = fn ($v) => call_user_func($f, $v);

        // null
        self::assertEquals('', $f(null));

        // bool
        self::assertEquals('是', $f(true));
        self::assertEquals('否', $f(false));

        // float
        self::assertEquals('100.00', $f(100.0));
        self::assertEquals('10,000.00', $f(10000.0));
        self::assertEquals('1.95', $f(1.954));
        self::assertEquals('1.95', $f(1.945));

        // DateTime
        $d = new DateTime('2011-01-02T05:03:01.012345');
        self::assertEquals('11-01-02 05:03', $f($d));
        $d = new DateTimeImmutable('2011-01-01T13:03:01.012345');
        self::assertEquals('11-01-01 13:03', $f($d));
        $d = new DateTimeImmutable('2011-01-01T00:00:00.0');
        self::assertEquals('11-01-01', $f($d));

        // other types
        self::assertEquals(' abc ', $f(' abc '));
        self::assertEquals('100', $f(100));
    }

    private static function getFilterFunc(string $name): Callable {
        $ext = new BungleTwigExtension();
        foreach ($ext->getFilters() as $filter) {
            if ($filter->getName() == $name) {
                return $filter->getCallable();
            }
        }
        throw new AssertionError("$name filter not found");
    }
}