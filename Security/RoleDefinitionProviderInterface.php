<?php

declare(strict_types=1);

namespace Bungle\Framework\Security;

/**
 * Provides RoleDefinitions will added to RoleRegistry.
 */
interface RoleDefinitionProviderInterface
{
    /**
     * iterate RoleDefinitions.
     */
    public function getRoleDefinitions(): iterable;
}
