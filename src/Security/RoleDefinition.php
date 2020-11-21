<?php

declare(strict_types=1);

namespace Bungle\Framework\Security;

use Bungle\Framework\StateMachine\Vina;

/**
 * Definition of a role,.
 *
 * Role name contains three parts, separate by '_'.
 *
 * The first part always 'ROLE' to match the conversion of symfony.
 *
 * The second part is the group name, role will grouped.
 *
 * For each entity object, the second part is role's high value,
 * third part is state machine action name.
 */
class RoleDefinition
{
    private string $name;
    private string $title;
    private string $description;
    private string $group;

    public function __construct(string $name, string $title, string $description, string $group)
    {
        $this->name = $name;
        $this->title = $title;
        $this->description = $description;
        $this->group = $group;
    }

    public static function newActionRole(string $high, string $action): string
    {
        return Vina::getTransitionRole($high, $action);
    }

    /**
     * Parse state machine action role name, returns array with two items:
     *
     * @return string[]
     */
    public static function parseActionRole(string $roleName): array
    {
        return array_slice(explode('_', $roleName), 1);
    }

    /**
     * Full name of the role, begin with ROLE_.
     */
    public function name(): string
    {
        return $this->name;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): string
    {
        return $this->description;
    }

    /**
     * RoleRegistry::getGroups() groups role definitions by result of getGroup().
     */
    public function getGroup(): string
    {
        return $this->group;
    }
}
