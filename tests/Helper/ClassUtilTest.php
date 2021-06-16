<?php

namespace Bungle\Framework\Tests\Helper;

use Bungle\Framework\Helper\ClassUtil;
use PHPUnit\Framework\TestCase;

class ClassUtilTest extends TestCase
{
    public function testGetShortClassName()
    {
        self::assertEquals('Foo', ClassUtil::getShortClassName('Foo'));
        self::assertEquals('Foo', ClassUtil::getShortClassName('\Foo'));
        self::assertEquals('Bar', ClassUtil::getShortClassName('Foo\Bar'));
        self::assertEquals('Bar', ClassUtil::getShortClassName('\Foo\Bar'));
    }
}
