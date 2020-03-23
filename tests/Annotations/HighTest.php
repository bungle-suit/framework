<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Annotations;

use Bungle\Framework\Annotation\High;
use PHPUnit\Framework\TestCase;

final class HighTest extends TestCase
{
    public function testLoadHigh(): void
    {
        self::assertEquals('ent', High::resolveHigh(Entity::class));
    }

    public function testLoadHighNotDefined(): void
    {
        self::assertNull(High::resolveHigh(HighTest::class));
    }

    public function testInvalidHighFormat(): void
    {
        self::expectException(\UnexpectedValueException::class);

        High::resolveHigh(InvalidHigh::class);
    }
}
