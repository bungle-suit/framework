<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\EventListener;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class FakeAuthorizationChecker implements AuthorizationCheckerInterface
{
    private array $wantedRoles;

    public function __construct(string ...$wantedRoles)
    {
        $this->wantedRoles = $wantedRoles;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted($attribute, $subject = null): bool
    {
        if (!is_string($attribute)) {
            return false;
        }

        return in_array($attribute, $this->wantedRoles);
    }
}
