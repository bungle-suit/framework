<?php

declare(strict_types=1);

namespace Bungle\Framework\Security;

use Bungle\Framework\FP;

class RoleRegistry
{
    /**
     * @var RoleDefinition[] $defs
     */
    private array $defs;
    private array $providers;

    /**
     * @param RoleDefinitionProviderInterface[] $providers
     */
    public function __construct(array $providers = [])
    {
        $this->providers = $providers;
    }

    private function add(RoleDefinition $roleDef): void
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
        $this->initDefs();
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
     * @return true if defs just initialized.
     */
    private function initDefs(): bool
    {
        if (!isset($this->defs)) {
            $this->defs = [];
            return true;
        }
        return false;
    }

    /**
     * @return RoleDefinition[]
     */
    public function getDefinitions(): array
    {
        if ($this->initDefs()) {
            foreach ($this->providers as $p) {
                $this->adds($p->getRoleDefinitions());
            }
        }
        return $this->defs;
    }

    /**
     * Return Definitions grouped by RoleDefinition group property.
     * @return RoleDefinition[]
     */
    public function getGroups(): array
    {
        return FP::group(FP::getter('getGroup'), $this->getDefinitions());
    }
}
