<?php

declare(strict_types=1);

namespace Bungle\Framework\Security;

class RoleRegistry
{
    /**
     * @var RoleDefinition[] $defs
     */
    private array $defs = [];

    /**
     * @param RoleDefinitionProviderInterface[] $providers
     */
    public function __construct(array $providers = [])
    {
        foreach ($providers as $p) {
            $this->adds($p->getRoleDefinitions());
        }
    }

    public function add(RoleDefinition $roleDef): void
    {
        assert(
            !self::roleExists($this->defs, $roleDef),
            "Duplicate role name: {$roleDef->name()}"
        );

        $this->defs[] = $roleDef;
    }

    /**
     * @param iterable|RoleDefinition[] $roleDefs
     */
    public function adds(iterable $roleDefs): void
    {
        foreach ($roleDefs as $item) {
            $this->add($item);
        }
    }

    /**
     * @param RoleDefinition[int] $arr
     */
    private static function roleExists(array $arr, RoleDefinition $role): bool
    {
        foreach ($arr as $item) {
            if ($item->name() === $role->name()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return RoleDefinition[]
     */
    public function getDefinitions(): array
    {
        return $this->defs;
    }
}
