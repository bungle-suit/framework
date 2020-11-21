<?php

declare(strict_types=1);

namespace Bungle\Framework\Security;

use Traversable;

/**
 * Provides RoleDefinitions will added to RoleRegistry.
 */
interface RoleDefinitionProviderInterface
{
    /**
     * iterate RoleDefinitions.
     * @return Traversable<RoleDefinition>
     */
    public function getRoleDefinitions(): Traversable;
}
