<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Code;

use Bungle\Framework\Ent\Code\UniqueName;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class UniqueNameTest extends MockeryTestCase
{
    public function testNext(): void
    {
        $namer = new UniqueName('foo', 2);
        self::assertEquals('foo2', $namer->next());
    }
}
