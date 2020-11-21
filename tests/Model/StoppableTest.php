<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Model;

use Bungle\Framework\Model\Stoppable;
use Bungle\Framework\Model\StoppableInterface;
use PHPUnit\Framework\TestCase;

class StoppableTest extends TestCase
{
    public function test(): void
    {
        $ctx = new class() implements StoppableInterface {
            use Stoppable;
        };

        self::assertFalse($ctx->isStopped());

        $ctx->stop();
        self::assertTrue($ctx->isStopped());

        $ctx->stop();
        self::assertTrue($ctx->isStopped());
    }
}
