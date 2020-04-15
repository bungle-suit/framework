<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Twig;

use AssertionError;
use Bungle\Framework\Twig\BungleTwigExtension;
use PHPUnit\Framework\TestCase;

class BungleTwigExtensionTest extends TestCase
{
    public function testFormat()
    {
        $f = self::getFilterFunc('bungle_format');

        // bool
        self::assertEquals('是', $f(true));
        self::assertEquals('否', $f(false));
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
