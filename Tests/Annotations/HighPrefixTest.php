<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Annotations;

use PHPUnit\Framework\TestCase;
use Bungle\Framework\Annotation\HighPrefix;
use Bungle\Framework\Annotation\AnnotationNotDefinedException;

final class HighPrefixTest extends TestCase
{
    public function testLoadHighPrefix(): void
    {
        self::assertEquals('ent', HighPrefix::loadHighPrefix(Entity::class));
    }

    public function testLoadHighPrefixNotDefined(): void
    {
        self::assertNull(HighPrefix::loadHighPrefix(HighPrefixTest::class));
    }

    public function testInvalidHighPrefixFormat(): void
    {
        self::expectException(\UnexpectedValueException::class);
        
        HighPrefix::loadHighPrefix(InvalidPrefix::class);
    }
}
