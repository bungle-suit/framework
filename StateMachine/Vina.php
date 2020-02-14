<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Symfony\Component\Workflow\Registry;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Bungle\Framework\StateMachine\EventListener\TransitionRoleGuardListener;

/**
 * Vina is a service help us to handle StateMachine
 * common operations.
 *
 * The name Vina considered to be the name of AI
 * Assistant.
 */
class Vina
{
    private Registry $registry;
    private AuthorizationCheckerInterface $authChecker;

    public function __construct(
        Registry $registry,
        AuthorizationCheckerInterface $authChecker
    ) {
        $this->registry = $registry;
        $this->authChecker = $authChecker;
    }

    /**
     * Returns associated array of transition name -> title
     * for StateMachine attached with $subject.
     */
    public function getTransitionTitles($subject): array
    {
        $sm = $this->registry->get($subject);
        $store = $sm->getMetadataStore();
        $r = [];
        foreach ($sm->getDefinition()->getTransitions() as $trans) {
            $meta = $store->getTransitionMetadata($trans);
            $r[$trans->getName()] = $meta['title'] ?? $trans->getName();
        }
        return $r;
    }

    /**
     * Return associated array of state/place name -> title
     * for StateMachine attached with $subject
     */
    public function getStateTitles($subject): array
    {
        $sm = $this->registry->get($subject);
        $store = $sm->getMetadataStore();
        $r = [];
        foreach ($sm->getDefinition()->getPlaces() as $place) {
            $meta = $store->getPlaceMetadata($place);
            $r[$place] = $meta['title'] ?? (
                $place == Entity::INITIAL_STATE ? '未保存' : $place
            );
        }
        return $r;
    }

    /**
     * Return names of possible transitions for current user of specific
     * subject/entity.
     *
     * Impossible means current user do not contains required role to
     * apply the transition. Such as Entity order, which high is `ord'
     * has a transition named 'save', If current user do have 'ROLE_ord_save'
     * then 'save' transition excluded from getPossibleTransitions() result.
     */
    public function getPossibleTransitions($subject): array
    {
        $sm = $this->registry->get($subject);
        $trans = $sm->getDefinition()->getTransitions();
        $r = [];
        foreach ($trans as $tr) {
            $role = TransitionRoleGuardListener::getTransitionRole(
                $sm->getName(),
                $tr->getName(),
            );
            if ($this->authChecker->isGranted($role)) {
                $r[]=$tr->getName();
            }
        }
        return $r;
    }
}
