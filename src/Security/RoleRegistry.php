<?php

declare(strict_types=1);

namespace Bungle\Framework\Security;

use Bungle\Framework\FP;
use LogicException;
use Webmozart\Assert\Assert;

class RoleRegistry
{
    /**
     * @var RoleDefinition[] $defs
     */
    private array $defs;
    /** @var RoleDefinitionProviderInterface[]  */
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
        if (self::roleExists($this->defs, $roleDef)) {
            throw new LogicException("Duplicate role name: {$roleDef->name()}");
        }

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
     * @param array<int, RoleDefinition> $arr
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
     * Return true if defs just initialized.
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
     * @phpstan-return array<string, RoleDefinition[]>
     */
    public function getGroups(): array
    {
        return FP::group(FP::getter('getGroup'), $this->getDefinitions());
    }

    /**
     * Get role definition by its name.
     */
    public function getByName(string $roleName): RoleDefinition
    {
        $r = FP::firstOrNull(fn (RoleDefinition $role) => $role->name() === $roleName, $this->defs);
        Assert::notNull($r, "no role named $roleName");
        return $r;
    }
}
