<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Code;

use Bungle\Framework\Ent\Code\CodeContext;
use LogicException;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AbstractSteppedGeneratorTest extends MockeryTestCase
{
    public function testGenerateResultNotSet(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("No code step set \$result.");

        $g = new ArraySteppedGenerator([]);
        $g->generate((object)[]);
    }

    public function testSteps(): void
    {
        $o = (object)['a' => 1];
        $g = new ArraySteppedGenerator([
            function (object $subject, CodeContext $ctx) use ($o): void {
                self::assertEquals($o, $subject);
                $ctx->result = 'foo';
            },
        ]);

        self::assertEquals('foo', $g->generate($o));
    }
}
