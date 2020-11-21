<?php

/** @noinspection PhpParamsInspection */
declare(strict_types=1);

namespace Bungle\Framework\Tests\Twig;

use AssertionError;
use Bungle\Framework\Ent\IDName\HighIDNameTranslator;
use Bungle\Framework\Ent\ObjectName;
use Bungle\Framework\FP;
use Bungle\Framework\Twig\BungleTwigExtension;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Twig\Node\Node;
use Twig\TwigFilter;

class BungleTwigExtensionTest extends MockeryTestCase
{
    /** @var Mockery\MockInterface|HighIDNameTranslator */
    private $highIDNameTranslator;
    private ArrayAdapter $cache;
    private ObjectName $objectName;

    public function testFormat(): void
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
        assert($f !== null);
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

    public function testJustify(): void
    {
        $filter = $this->getFilterFunc('justify');
        self::assertEquals('订　单', $filter('订单', 3));
    }

    public function testObjectName(): void
    {
        $filter = $this->getFilterFunc('object_name');
        self::assertEquals('BungleTwigExtensionTest', $filter($this));
    }

    private function getFilter(string $name): TwigFilter
    {
        $this->cache = new ArrayAdapter();
        $this->objectName = new ObjectName($this->cache);
        $this->highIDNameTranslator = Mockery::mock(HighIDNameTranslator::class);
        $ext = new BungleTwigExtension($this->highIDNameTranslator, $this->objectName);

        foreach ($ext->getFilters() as $filter) {
            if ($filter->getName() == $name) {
                return $filter;
            }
        }
        throw new AssertionError("$name filter not found");
    }

    private function getFilterFunc(string $name): callable
    {
        /** @var callable $r */
        $r = FP::notNull($this->getFilter($name)->getCallable());
        return $r;
    }

    public function testUniqueId(): void
    {
        $this->cache = new ArrayAdapter();
        $this->objectName = new ObjectName($this->cache);
        $this->highIDNameTranslator = Mockery::mock(HighIDNameTranslator::class);
        $ext = new BungleTwigExtension($this->highIDNameTranslator, $this->objectName);
        self::assertEquals('__uid_1', $ext->uniqueId());
        self::assertEquals('__uid_2', $ext->uniqueId());
        self::assertEquals('__uid_3', $ext->uniqueId());
    }
}
