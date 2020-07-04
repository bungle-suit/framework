<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent;

use Bungle\Framework\Ent\ObjectName;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Name For Test
 *
 * Long Description
 */
class ObjectNameTest extends MockeryTestCase
{
    public function test(): void
    {
        $cache = new ArrayAdapter();
        $name = new ObjectName($cache);

        self::assertEquals('Name For Test', $name->getName(self::class));
        self::assertEquals('Name For Test', $name->getName($this));
        self::assertEquals('NoDocCommentClass2', $name->getName(NoDocCommentClass2::class));
        self::assertEquals('NoDocCommentClass', $name->getName(NoDocCommentClass::class));
    }
}

class NoDocCommentClass
{
}

class NoDocCommentClass2
{
}
