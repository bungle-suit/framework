<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Code;

use Bungle\Framework\Ent\Code\CodeGenerator2;
use Bungle\Framework\Tests\DBTestable;
use DateTime;
use PHPUnit\Framework\TestCase;
use RangeException;

class CodeGenerator2Test extends TestCase
{
    use DBTestable {
        setUp as baseSetup;
    }

    protected function setUp(): void
    {
        $this->baseSetup();
        $coll = $this->db->selectCollection(CodeGenerator2::ID_COLLECTION);
        $coll->drop();
    }

    public function testNextPrefixedCode()
    {
        $gen = new CodeGenerator2($this->dm);
        self::assertEquals('foo001', $gen->nextPrefixedCode('foo', 3));
        self::assertEquals('bar1', $gen->nextPrefixedCode('bar', 1));
        self::assertEquals('foo002', $gen->nextPrefixedCode('foo', 3));
        self::assertEquals('foo003', $gen->nextPrefixedCode('foo', 3));
    }

    public function testNextPrefixedCodeOutOfRange(): void
    {
        $gen = new CodeGenerator2($this->dm);
        for ($i = 0; $i < 9; $i++) {
            $gen->nextPrefixedCode('foo', 1);
        }

        $this->expectException(RangeException::class);
        $gen->nextPrefixedCode('foo', 1);
    }

    public function testCompactYearMonth(): void
    {
        $recs = [
            '2019-01-02' => '191',
            '2020-10-02' => '20X',
            '2020-11-02' => '20Y',
            '2020-12-02' => '20Z',
        ];
        foreach ($recs as $sd => $exp) {
            $d = new DateTime($sd);
            self::assertEquals($exp, CodeGenerator2::compactYearMonth($d));
        }
    }
}
