<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry;

use Bungle\Framework\Inquiry\ColumnMeta;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\PropertyInfo\Type;

class ColumnMetaTest extends MockeryTestCase
{
    public function testOptions(): void
    {
        $t = new Type(Type::BUILTIN_TYPE_INT, true);
        $col = new ColumnMeta('foo', 'bar', $t, ['opt1' => 2, 'opt2' => 3]);

        self::assertEquals(2, $col->get('opt1'));
        self::assertEquals(3, $col->get('opt2'));
    }
}
