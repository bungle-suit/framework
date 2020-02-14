<?php
declare(strict_types=1);

namespace Bungle\Framework\Security;

/**
 * Provides RoleDefinitions stored in array, useful for unit tests
 * and simple fixed use cases.
 */
final class ArrayRoleDefinitionProvider implements RoleDefinitionProviderInterface
{
    private array $defs;

    public function __construct(array $defs)
    {
        $this->defs = $defs;
    }

    public function getRoleDefinitions(): iterable
    {
        return $this->defs;
    }
}
