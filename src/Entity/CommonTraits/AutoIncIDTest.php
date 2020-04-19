<?php
declare(strict_types=1);

namespace Bungle\Framework\Entity\CommonTraits;

use PHPUnit\Framework\TestCase;

class AutoIncIDTest extends TestCase
{
    public function testGetId()
    {
        $doc = new class() {
            use AutoIncID;
        };

        self::assertEquals(0, $doc->getId());
    }
}
