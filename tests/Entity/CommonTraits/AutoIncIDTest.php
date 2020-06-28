<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Entity\CommonTraits;

use Bungle\Framework\Entity\CommonTraits\AutoIncID;
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
