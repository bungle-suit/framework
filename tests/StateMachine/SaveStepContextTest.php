<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine;

use Bungle\Framework\StateMachine\SaveStepContext;
use PHPUnit\Framework\TestCase;

class SaveStepContextTest extends TestCase
{
    public function testInitialAttrs(): void
    {
        $attrs = [
            'attr1'=> 'foo',
            'attr2' => 'bar',
        ];
        $ctx = new SaveStepContext($attrs);

        self::assertEquals($attrs, $ctx->all());
    }
}
