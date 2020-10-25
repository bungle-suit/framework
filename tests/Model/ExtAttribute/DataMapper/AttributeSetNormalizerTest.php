<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute\DataMapper;

use Bungle\Framework\Form\DataMapper\AttributeSetNormalizer;
use Bungle\Framework\Tests\Model\ExtAttribute\TestAttribute;
use Bungle\Framework\Tests\Model\ExtAttribute\TestNormalizedAttributeSet;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AttributeSetNormalizerTest extends MockeryTestCase
{
    private AttributeSetNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new AttributeSetNormalizer(TestNormalizedAttributeSet::class);
    }

    public function testTransform(): void
    {
        $normalized = $this->normalizer->transform([]);
        self::assertInstanceOf(TestNormalizedAttributeSet::class, $normalized);
        self::assertEquals(
            ['a' => false, 'b' => false, 'c' => ''],
            $normalized->toArray()
        );
        self::assertEquals('', $normalized['c']);

        $normalized = $this->normalizer->transform(
            [
                'a' => new TestAttribute('a', '1'),
                'c' => new TestAttribute('c', 'blah'),
            ]
        );
        self::assertEquals(['a' => true, 'b' => false, 'c' => 'blah'], $normalized->toArray());
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
