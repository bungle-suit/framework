<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests;

use Bungle\Framework\FP;
use PHPUnit\Framework\TestCase;

class FPTest extends TestCase
{
    public function testAttr()
    {
        $f = FP::attr('name');
        $o = (object)['id'=>1,'name'=>'foo'];
        self::assertEquals('foo', $f($o));
    }
}
