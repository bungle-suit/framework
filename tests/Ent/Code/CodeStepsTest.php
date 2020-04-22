<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Code;

use Bungle\Framework\Ent\Code\CodeContext;
use Bungle\Framework\Ent\Code\CodeSteps;
use DateTime;
use PHPUnit\Framework\TestCase;

class CodeStepsTest extends TestCase
{
    public function testCompactYearMonth()
    {
        $recs = [
            '2019-01-02' => '191',
            '2020-10-02' => '20X',
            '2020-11-02' => '20Y',
            '2020-12-02' => '20Z',
        ];
        $o = (object)[];
        $ctx = new CodeContext();
        foreach ($recs as $sd => $exp) {
            $d = new DateTime($sd);
            CodeSteps::compactYearMonth($o, $ctx, $d);
        }
        self::assertEquals(['191', '20X', '20Y', '20Z'], $ctx->sections);

        // ensure COMPACT_YEAR_MONTH const is valid.
        $ctx = new CodeContext();
        $f = CodeSteps::COMPACT_YEAR_MONTH;
        $f($o, $ctx);
        self::assertNotEmpty($ctx->sections);
    }

    public function testLiteral(): void
    {
        $s1 = CodeSteps::literal('Foo');
        $s2 = CodeSteps::literal('Bar');
        $ctx = new CodeContext();
        $s1((object)[], $ctx);
        $s2((object)[], $ctx);

        self::assertEquals(['Foo', 'Bar'], $ctx->sections);
    }
}
