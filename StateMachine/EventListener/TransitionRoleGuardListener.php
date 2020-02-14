<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine\EventListener;

use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
        $role = self::getTransitionRole(
            $event->getWorkflowName(),
            $event->getTransition()->getName(),
        );

        if (!$this->authChecker->isGranted($role, $event->getSubject())) {
            $event->setBlocked(true);
        }
    }

    public static function getTransitionRole(string $workflowName, string $transitionName): string
    {
        return 'ROLE_'.$workflowName.'_'.$transitionName;
    }
}
