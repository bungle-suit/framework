<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\IDName;

use Bungle\Framework\Ent\IDName\AbstractIDNameTranslator;
use Bungle\Framework\FP;
use LogicException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\Cache\CacheInterface;

class AbstractIDNameTranslatorTest extends MockeryTestCase
{
    private ArrayAdapter $cache;
    private AbstractIDNameTranslator $idName;
    public array $callTimes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->callTimes = [];
        $this->cache = new ArrayAdapter();
        $this->idName = new class('usr', $this->cache, $this) extends AbstractIDNameTranslator {
            private array $callTimes;
            private AbstractIDNameTranslatorTest $self;

            public function __construct(
                string $high,
                CacheInterface $cache,
                AbstractIDNameTranslatorTest $self
            ) {
                parent::__construct($high, $cache);
                $this->self = $self;
            }

            protected function doIdToName($id): string
            {
                $this->self->callTimes[$id] = $this->self->callTimes[$id] ?? 0 + 1;
                if ($id === 'foo') {
                    return 'bar';
                } elseif ($id === 33) {
                    return 'Thirty three';
                } elseif ($id === 'empty') {
                    return '';
                }
                throw new LogicException('failed to get name');
            }
        };
    }

    public function testNullIdToName(): void
    {
        self::assertEquals('', $this->idName->idToName(null));
    }

    public function testIdToName()
    {
        self::assertEquals('bar', $this->idName->idToName('foo'));
        self::assertEquals('bar', $this->idName->idToName('foo'));
        self::assertEquals(1, $this->callTimes['foo']);
        self::assertEquals(
            'bar',
            $this->cache->get(
                $this->idName->getCacheKey('foo'),
                [FP::class, 'identity']
            )
        );

        self::assertEquals('Thirty three', $this->idName->idToName(33));
        self::assertEquals('', $this->idName->idToName('empty'));
    }

    public function testGetCacheKey(): void
    {
        self::assertEquals('IdName-usr-1', $this->idName->getCacheKey(1));
        self::assertEquals('IdName-usr-foo', $this->idName->getCacheKey('foo'));
    }
}
