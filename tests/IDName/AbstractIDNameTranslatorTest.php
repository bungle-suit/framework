<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\IDName;

use Bungle\Framework\IDName\AbstractIDNameTranslator;
use LogicException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class AbstractIDNameTranslatorTest extends MockeryTestCase
{
    public function testIdToName()
    {
        $cache = new ArrayAdapter();
        $idName = new class($cache) extends AbstractIDNameTranslator {
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
        self::assertEquals('bar', $idName->idToName('foo'));
        self::assertEquals('bar', $idName->idToName('foo'));
        self::assertEquals(1, $idName->callTimes['foo']);

        self::assertEquals('Thirty three', $idName->idToName(33));
        self::assertEquals('', $idName->idToName('empty'));
    }
}
