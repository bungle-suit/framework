<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Code;

use Bungle\Framework\Ent\Code\CodeContext;
use Bungle\Framework\Ent\Code\PrefixedAutoIncCode;
use Bungle\Framework\Tests\DBTestable;
use LogicException;
use PHPUnit\Framework\TestCase;

class PrefixedAutoIncCodeTest extends TestCase
{
    use DBTestable {
        setUp as baseSetup;
    }

    protected function setUp(): void
    {
        $this->baseSetup();

        $coll = $this->db->selectCollection(PrefixedAutoIncCode::ID_COLLECTION);
        $coll->drop();
    }

    public function test()
    {
        $ctx = new CodeContext();
        $o = (object)[];
        $cases = [
            ['foo', 3, 'foo001'],
            ['bar', 1, 'bar1'],
            ['foo', 3, 'foo002'],
            ['foo', 3, 'foo003'],
        ];
        foreach ($cases as list($prefix, $n, $exp)) {
            $gen = new PrefixedAutoIncCode($this->dm, $n);
            $ctx->result = $prefix;
            $gen($o, $ctx);
            self::assertEquals($exp, $ctx->result);
        }
    }

    public function testExistResultIsEmpty(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('PrefixedCodeAutoIncCode prefix/$result should not be empty');
        $gen = new PrefixedAutoIncCode($this->dm, 2);
        $gen((object)[], new CodeContext());
    }
}
