<?php
/** @noinspection PhpParamsInspection */
declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\IDName;

use Bungle\Framework\Ent\IDName\HighIDNameTranslator;
use Bungle\Framework\Ent\IDName\HighIDNameTranslatorChain;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class HighIDNameTranslatorTest extends MockeryTestCase
{
    public function testIdToName(): void
    {
        $chain = Mockery::mock(HighIDNameTranslatorChain::class);
        $cache = new ArrayAdapter();
        $chain->expects('translate')->with('ord', 33)->andReturn('foo');
        $chain->expects('translate')->with('ord', 0)->andReturn('blah');
        $chain->expects('translate')->with('prd', '')->andReturn('bar');

        $idName = new HighIDNameTranslator($chain, $cache);
        // null value not passed to translator.
        self::assertEquals('', $idName->idToName('ord', null));

        self::assertEquals('foo', $idName->idToName('ord', 33));
        // should cached.
        self::assertEquals('foo', $idName->idToName('ord', 33));
        // 0 is not null.
        self::assertEquals('blah', $idName->idToName('ord', 0));
        // '' empty string also not null
        self::assertEquals('bar', $idName->idToName('prd', ''));
    }

    public function testGetCacheKey(): void
    {
        $chain = Mockery::mock(HighIDNameTranslatorChain::class);
        $cache = new ArrayAdapter();
        $idName = new HighIDNameTranslator($chain, $cache);

        self::assertEquals('highIDName-usr-33', $idName->getCacheKey('usr', 33));
        self::assertEquals('highIDName-prd-foo', $idName->getCacheKey('prd', 'foo'));
    }
}
