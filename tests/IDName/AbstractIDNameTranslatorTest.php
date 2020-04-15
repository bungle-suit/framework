<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\IDName;

use Bungle\Framework\FP;
use Bungle\Framework\IDName\AbstractIDNameTranslator;
use LogicException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class AbstractIDNameTranslatorTest extends MockeryTestCase
{
    private ArrayAdapter $cache;
    private AbstractIDNameTranslator $idName;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = new ArrayAdapter();
        $this->idName = new class('usr', $this->cache) extends AbstractIDNameTranslator {
            public array $callTimes = [];

            protected function doIdToName($id): string
            {
                $this->callTimes[$id] = $this->callTimes[$id] ?? 0 + 1;
                if ($id === 'foo') {
                    return 'bar';
                } else if ($id === 33) {
                    return 'Thirty three';
                } else if ($id === 'empty') {
                    return '';
                }
                throw new LogicException('failed to get name');
            }
        };
    }

    public function testIdToName()
    {
        self::assertEquals('bar', $this->idName->idToName('foo'));
        self::assertEquals('bar', $this->idName->idToName('foo'));
        self::assertEquals(1, $this->idName->callTimes['foo']);
        self::assertEquals('bar', $this->cache->get($this->idName->getCacheKey('foo'), [FP::class, 'identity']));

        self::assertEquals('Thirty three', $this->idName->idToName(33));
        self::assertEquals('', $this->idName->idToName('empty'));
    }

    public function testGetCacheKey(): void
    {
        self::assertEquals('IdName-usr-1', $this->idName->getCacheKey(1));
        self::assertEquals('IdName-usr-foo', $this->idName->getCacheKey('foo'));
    }
}
