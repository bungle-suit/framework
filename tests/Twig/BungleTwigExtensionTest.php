<?php
/** @noinspection PhpParamsInspection */
declare(strict_types=1);

namespace Bungle\Framework\Tests\Twig;

use AssertionError;
use Bungle\Framework\Ent\IDName\HighIDNameTranslator;
use Bungle\Framework\Twig\BungleTwigExtension;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Twig\Node\Node;
use Twig\TwigFilter;

class BungleTwigExtensionTest extends MockeryTestCase
{
    /** @var Mockery\MockInterface | Mockery\LegacyMockInterface | HighIDNameTranslator */
    private $highIDNameTranslator;

    public function testFormat()
    {
        $f = $this->getFilterFunc('bungle_format');

        // bool
        self::assertEquals('是', $f(true));
        self::assertEquals('否', $f(false));
    }

    public function testOdmEncodeJson(): void
    {
        $filter = $this->getFilter('odm_encode_json');
        self::assertEquals(['js'], $filter->getSafe($this->createMock(Node::class)));

        $f = $filter->getCallable();
        self::assertEquals('null', $f(null));
        self::assertEquals('[1,null]', $f([1, null]));
        self::assertEquals('[1,"汉字"]', BungleTwigExtension::odmEncodeJson([1, '汉字']));
    }

    public function testIDName(): void
    {
        $filter = $this->getFilterFunc('id_name');
        $this->highIDNameTranslator->expects('idToName')->with('ord', 33)->andReturn('foo');

        self::assertEquals('foo', $filter(33, 'ord'));

    }

    private function getFilter(string $name): TwigFilter
    {
        $this->highIDNameTranslator = Mockery::mock(HighIDNameTranslator::class);
        $ext = new BungleTwigExtension($this->highIDNameTranslator);

        foreach ($ext->getFilters() as $filter) {
            if ($filter->getName() == $name) {
                return $filter;
            }
        }
        throw new AssertionError("$name filter not found");
    }

    private function getFilterFunc(string $name): callable
    {
        return $this->getFilter($name)->getCallable();
    }
}
