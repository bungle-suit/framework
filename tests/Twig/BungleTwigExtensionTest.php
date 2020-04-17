<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Twig;

use AssertionError;
use Bungle\Framework\Twig\BungleTwigExtension;
use PHPUnit\Framework\TestCase;
use Twig\Node\Node;
use Twig\TwigFilter;

class BungleTwigExtensionTest extends TestCase
{
    public function testFormat()
    {
        $f = self::getFilterFunc('bungle_format');

        // bool
        self::assertEquals('是', $f(true));
        self::assertEquals('否', $f(false));
    }

    public function testOdmEncodeJson(): void
    {
        $filter = self::getFilter('odm_encode_json');
        self::assertEquals(['js'], $filter->getSafe($this->createMock(Node::class)));

        $f = $filter->getCallable();
        self::assertEquals('null', $f(null));
        self::assertEquals('[1,null]', $f([1, null]));
        self::assertEquals('[1,"汉字"]', BungleTwigExtension::odmEncodeJson([1, '汉字']));
    }

    private static function getFilter(string $name): TwigFilter
    {
        $ext = new BungleTwigExtension();
        foreach ($ext->getFilters() as $filter) {
            if ($filter->getName() == $name) {
                return $filter;
            }
        }
        throw new AssertionError("$name filter not found");
    }

    private static function getFilterFunc(string $name): callable
    {
        return self::getFilter($name)->getCallable();
    }
}
