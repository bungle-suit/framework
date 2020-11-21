<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Entity;

use Bungle\Framework\Entity\EntityUtils;
use Bungle\Framework\Tests\StateMachine\Entity\Order;
use DomainException;
use PHPUnit\Framework\TestCase;

class EntityCtorHasArgument // phpcs:ignore
{
    private int $id;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }
}

final class EntityUtilsTest extends TestCase // phpcs:ignore
{
    public function testCreate(): void
    {
        self::assertInstanceOf(Order::class, EntityUtils::create(Order::class));
    }

    public function testCreateFailedCtorHasArguments(): void
    {
        $this->expectException(DomainException::class);
        EntityUtils::create(EntityCtorHasArgument::class);
    }
}
