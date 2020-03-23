<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine\EventListener;

use Bungle\Framework\StateMachine\Vina;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * Implement role guard for workflow that test
 * current user has/not has `high`/`transitionName`
 * role. If not block current transition.
 *
 * TransitionEventListener configured by current
 * bundle, application need do nothing to enable this.
 */
final class TransitionRoleGuardListener
{
    private AuthorizationCheckerInterface $authChecker;

    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    public function __invoke(GuardEvent $event): void
    {
        $role = Vina::getTransitionRole(
            $event->getWorkflowName(),
            $event->getTransition()->getName(),
        );

        if (!$this->authChecker->isGranted($role, $event->getSubject())) {
            $event->setBlocked(true);
        }
    }
}
