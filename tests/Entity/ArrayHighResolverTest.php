<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Entity;

use Bungle\Framework\Entity\ArrayHighResolver;
use PHPUnit\Framework\TestCase;

final class ArrayHighResolverTest extends TestCase
{
    public function test(): void
    {
        $resolver = new ArrayHighResolver(
            [
                Order::class => 'ord',
                OrderLine::class => 'lne',
            ]
        );

        self::assertEquals('ord', $resolver->resolveHigh(Order::class));
        self::assertEquals('lne', $resolver->resolveHigh(OrderLine::class));
        self::assertNull($resolver->resolveHigh('foo'));
    }
}
