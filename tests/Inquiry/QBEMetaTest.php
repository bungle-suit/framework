<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry;

use Bungle\Framework\Inquiry\QBEMeta;
use LogicException;
use Symfony\Component\PropertyInfo\Type;

it(
    'qbe type should allow null',
    function () {
        $t = new Type(Type::BUILTIN_TYPE_INT, true);
        $qbe = new QBEMeta('foo', 'lbl', $t);
        expect($qbe->getName())->toEqual('foo');
        expect($qbe->getLabel())->toEqual('lbl');
        expect($qbe->getType())->toEqual($t);

        new QBEMeta('bar', 'lbl', new Type(Type::BUILTIN_TYPE_INT));
    }
)->throws(LogicException::class, 'QBE value must allow null. (bar)');

it(
    'qbe options',
    function () {
        $t = new Type(Type::BUILTIN_TYPE_INT, true);
        $qbe = new QBEMeta('foo', 'lbl', $t, ['opt1' => 2, 'opt2' => 3]);

        expect($qbe->get('opt1'))->toEqual(2);
        expect($qbe->get('opt2'))->toEqual(3);
    }
);
