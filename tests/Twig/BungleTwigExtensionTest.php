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
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Node\Node;
use Twig\TwigFilter;

class BungleTwigExtensionTest extends MockeryTestCase
{
    private HighIDNameTranslator|LegacyMockInterface|MockInterface $highIDNameTranslator;
    private ArrayAdapter $cache;
    private ObjectName $objectName;
    private LegacyMockInterface|SerializerInterface|MockInterface $serializer;
    private BungleTwigExtension $ext;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = new ArrayAdapter();
        $this->objectName = new ObjectName($this->cache);
        $this->highIDNameTranslator = Mockery::mock(HighIDNameTranslator::class);
        $this->serializer = Mockery::mock(SerializerInterface::class);
        $this->ext = new BungleTwigExtension($this->highIDNameTranslator, $this->objectName, $this->serializer);
    }

    public function testFormat(): void
    {
        $f = $this->getFilterFunc('bungle_format');

        // bool
        self::assertEquals('是', $f(true));
        self::assertEquals('否', $f(false));
    }

    public function testOdmEncodeJson(): void
    {
        $this->serializer->expects('serialize')->with(null, 'json')->andReturn('null');
        $filter = $this->getFilter('odm_encode_json');
        self::assertEquals(['js'], $filter->getSafe($this->createMock(Node::class)));

        $f = $filter->getCallable();
        assert($f !== null);
        self::assertEquals('null', $f(null));
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
        foreach ($this->ext->getFilters() as $filter) {
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
        self::assertEquals('__uid_1', $this->ext->uniqueId());
        self::assertEquals('__uid_2', $this->ext->uniqueId());
        self::assertEquals('__uid_3', $this->ext->uniqueId());
    }
}
