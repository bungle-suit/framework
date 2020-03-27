<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Security;

use Bungle\Framework\Security\RoleDefinition;
use PHPUnit\Framework\TestCase;

final class RoleDefinitionTest extends TestCase
{
    public function testNewActionRole(): void
    {
        self::assertEquals('R_Foo_Bar', RoleDefinition::newActionRole('Foo', 'Bar'));
    }

    public function testParseActionRole(): void
    {
        self::assertEquals(['Foo', 'Bar'], RoleDefinition::parseActionRole('ROLE_Foo_Bar'));
    }
}
