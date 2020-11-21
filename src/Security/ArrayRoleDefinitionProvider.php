<?php

declare(strict_types=1);

namespace Bungle\Framework\Security;

use ArrayIterator;
use Traversable;

/**
 * Provides RoleDefinitions stored in array, useful for unit tests
 * and simple fixed use cases.
 */
final class ArrayRoleDefinitionProvider implements RoleDefinitionProviderInterface
{
    /** @var RoleDefinition[] */
    private array $defs;

    /**
     * @param RoleDefinition[] $defs
     */
    public function __construct(array $defs)
    {
        $this->defs = $defs;
    }

    public function getRoleDefinitions(): Traversable
    {
        return new ArrayIterator($this->defs);
    }
}
