<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Security;

use AssertionError;
use Bungle\Framework\Security\ArrayRoleDefinitionProvider;
use Bungle\Framework\Security\RoleDefinition;
use Bungle\Framework\Security\RoleRegistry;
use PHPUnit\Framework\TestCase;

final class RoleRegistryTest extends TestCase
{
    public function testRoleDefinitionProviders(): void
    {
        $reg = new RoleRegistry([
          new ArrayRoleDefinitionProvider([
            $r1 = new RoleDefinition('a', '', '', ''),
            $r2 = new RoleDefinition('b', '', '', ''),
          ]),
          new ArrayRoleDefinitionProvider([]),
          new ArrayRoleDefinitionProvider([
            $r3 = new RoleDefinition('c', '', '', ''),
          ]),
        ]);

        self::assertEquals([$r1, $r2, $r3], $reg->getDefinitions());
    }

    public function testAdd(): RoleRegistry
    {
        $reg = new RoleRegistry();
        self::assertEmpty($reg->getDefinitions());
        $reg->adds([$r1 = new RoleDefinition('ROLE_1_1', '', '', '')]);
        $reg->adds([$r2 = new RoleDefinition('ROLE_1_2', '', '', '')]);
        self::assertEquals([$r1, $r2], $reg->getDefinitions());

        return $reg;
    }

    /**
     * @depends testAdd
     */
    public function testAddCheckDupName(RoleRegistry $reg): void
    {
        $this->expectException(AssertionError::class);
        $reg->adds([new RoleDefinition('ROLE_1_1', 'a', 'b', '')]);
    }

    /**
     * @depends testAdd
     */
    public function testAdds(RoleRegistry $reg): void
    {
        $expRoles = $reg->getDefinitions();
        $reg->adds([
          $r3 = new RoleDefinition('ROLE_2_1', '', '', ''),
          $r4 = new RoleDefinition('ROLE_2_2', '', '', ''),
        ]);

        self::assertEquals(array_merge($expRoles, [$r3, $r4]), $reg->getDefinitions());
    }

    public function testGetGroups(): void
    {
        $reg = new RoleRegistry();
        $reg->adds([
            $ra1 = new RoleDefinition('ROLE_2_1', '', '', 'ga'),
            $rb1 = new RoleDefinition('ROLE_3_1', '', '', 'gb'),
            $ra2 = new RoleDefinition('ROLE_2_2', '', '', 'ga'),
            $rb2 = new RoleDefinition('ROLE_3_2', '', '', 'gb'),
        ]);
        $exp = [
            'ga' => [$ra1, $ra2],
            'gb' => [$rb1, $rb2],
        ];
        self::assertEquals($exp, $reg->getGroups());
    }
}
