<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\StateMachine\STT\EntityAccessControlInterface;
use Bungle\Framework\StateMachine\STTLocator\STTLocatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Checks does current user can view/load specific entity object.
 *
 * By default as long as user has ROLE_high_view role, passes
 * the FSMViewVoter.
 *
 * STT implement EntityAccessControlInterface to custom the logic,
 * such as owner logic.
 */
class FSMViewVoter extends Voter
{
    private STTLocatorInterface $sttLocator;
    private EntityRegistry $registry;

    public function __construct(
        STTLocatorInterface $STTLocator,
        EntityRegistry $registry
    ) {
        $this->sttLocator = $STTLocator;
        $this->registry = $registry;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return 'view' === $attribute && $subject instanceof StatefulInterface;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $high = $this->registry->getHigh(get_class($subject));
        $role = Vina::getTransitionRole($high, 'view');
        if (!in_array($role, $token->getRoleNames())) {
            return false;
        }

        $stt = $this->sttLocator->getSTTForClass(get_class($subject));
        if ($stt instanceof EntityAccessControlInterface) {
            foreach ($stt->canAccess() as $step) {
                if (!$step($subject)) {
                    return false;
                }
            }
        }

        return true;
    }
}
