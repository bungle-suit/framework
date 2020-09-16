<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry;

use Bungle\Framework\Inquiry\QBEMeta;
use LogicException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\PropertyInfo\Type;

class QBEMetaTest extends MockeryTestCase
{
    public function testTypeShouldAllowNull(): void
    {
        $t = new Type(Type::BUILTIN_TYPE_INT, true);
        $qbe = new QBEMeta('foo', 'lbl', $t);
        self::assertEquals('foo', $qbe->getName());
        self::assertEquals('lbl', $qbe->getLabel());

        self::assertEquals($t, $qbe->getType());


        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("QBE value must allow null. (bar)");
        new QBEMeta('bar', 'lbl', new Type(Type::BUILTIN_TYPE_INT));
    }

    public function testOptions(): void
    {
        $t = new Type(Type::BUILTIN_TYPE_INT, true);
        $qbe = new QBEMeta('foo', 'lbl', $t, ['opt1' => 2, 'opt2' => 3]);

        self::assertEquals(2, $qbe->get('opt1'));
        self::assertEquals(3, $qbe->get('opt2'));
    }
}
