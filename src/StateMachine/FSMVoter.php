<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Bungle\Framework\StateMachine\STT\EntityAccessControlInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FSMVoter extends Voter
{
    private STTLocator\STTLocatorInterface $sttLocator;

    public function __construct(STTLocator\STTLocatorInterface $STTLocator)
    {
        $this->sttLocator = $STTLocator;
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (!$subject instanceof StatefulInterface) {
            return false;
        }

        return $attribute === 'view';
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
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
