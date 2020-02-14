<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Bungle\Framework\Entity\ArrayHighResolver;

final class ArrayHighResolverTest extends TestCase
{
    public function test(): void
    {
        $resolver = new ArrayHighResolver([
        'order\\order' => 'ord',
        'order\\line' => 'lne',
        ]);

        self::assertEquals('ord', $resolver->resolveHigh('order\\order'));
        self::assertEquals('lne', $resolver->resolveHigh('order\\line'));
        self::assertNull($resolver->resolveHigh('foo'));
    }
}
