<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry;

use Bungle\Framework\Inquiry\QBEMeta;
use LogicException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\PropertyInfo\Type;

class QBEMetaTest extends MockeryTestCase
{
    public function testQBETypeShouldAllowNull(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('QBE value must allow null. (bar)');

        $t = new Type(Type::BUILTIN_TYPE_INT, true);
        $qbe = new QBEMeta('foo', 'lbl', $t);
        expect($qbe->getName())->toEqual('foo');
        expect($qbe->getLabel())->toEqual('lbl');
        expect($qbe->getType())->toEqual($t);

        new QBEMeta('bar', 'lbl', new Type(Type::BUILTIN_TYPE_INT));
    }

    public function testQBEOptions(): void
    {
        $t = new Type(Type::BUILTIN_TYPE_INT, true);
        $qbe = new QBEMeta('foo', 'lbl', $t, ['opt1' => 2, 'opt2' => 3]);

        expect($qbe->get('opt1'))->toEqual(2);
        expect($qbe->get('opt2'))->toEqual(3);
    }

    public function testInitialQbeValue(): void
    {
        $t = new Type(Type::BUILTIN_TYPE_INT, true);
        $qbe = new QBEMeta('foo', 'lbl', $t);

        // default to be null
        expect($qbe->getInitialQBEValue())->toBeNull();

        // set explicitly
        $qbe->setInitialValue(123);
        expect($qbe->getInitialQBEValue())->toBe(123);

        // use callback
        $qbe->set('foo', 456);
        $qbe->setInitialValue(fn(QBEMeta $x) => $x->get('foo'));
        expect($qbe->getInitialQBEValue())->toBe(456);
    }
}
