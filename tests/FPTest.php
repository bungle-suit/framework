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

    public function testGetter(): void
    {
        $o = new class {
            public function getName() {
                return 'bar';
            }
        };
        $f = FP::getter('getName');
        self::assertEquals('bar', $f($o));

    }
}
