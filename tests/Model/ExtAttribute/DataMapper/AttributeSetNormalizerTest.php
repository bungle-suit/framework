<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute\DataMapper;

use Bungle\Framework\Form\DataMapper\AttributeSetNormalizer;
use Bungle\Framework\Model\ExtAttribute\BoolAttribute;
use Bungle\Framework\Model\ExtAttribute\StringAttribute;
use Bungle\Framework\Tests\Model\ExtAttribute\TestAttribute;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AttributeSetNormalizerTest extends MockeryTestCase
{
    private AttributeSetNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $defs = [
            new BoolAttribute('a', 'Foo', 'Foo Desc'),
            new BoolAttribute('b', 'Bar', 'Bar Desc'),
            new StringAttribute('c', 'Foobar', 'Foobar Desc'),
        ];
        $this->normalizer = new AttributeSetNormalizer($defs, fn (string $name) => new TestAttribute($name));
    }

    public function testTransform(): void
    {
        $normalized = $this->normalizer->transform([]);
        self::assertEquals(
            ['a' => false, 'b' => false, 'c' => ''],
            $normalized
        );
        self::assertEquals('', $normalized['c']);

        $normalized = $this->normalizer->transform(
            [
                'a' => new TestAttribute('a', '1'),
                'c' => new TestAttribute('c', 'blah'),
            ]
        );
        self::assertEquals(['a' => true, 'b' => false, 'c' => 'blah'], $normalized);
    }

    public function testReverseTransform(): void
    {
        // case 1: value change to non-default
        $normalized = $this->normalizer->transform([]);
        $normalized['c'] = 'foo';
        $normalized['a'] = true;

        self::assertEquals(
            [
                new TestAttribute('a', '1'),
                new TestAttribute('c', 'foo') ],
            $this->normalizer->reverseTransform($normalized)
        );

        // case 2: value change to default
        $normalized['a'] = false;
        self::assertEquals(
            [ new TestAttribute('c', 'foo')],
            $this->normalizer->reverseTransform($normalized)
        );
    }
}
