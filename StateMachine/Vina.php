<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Symfony\Component\Workflow\Registry;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Exception\TransitionException;
use Symfony\Component\Workflow\WorkflowInterface;

/**
 * Vina is a service help us to handle StateMachine
 * common operations.
 *
 * The name Vina considered to be the name of AI
 * Assistant.
 */
class Vina
{
    // TODO: remove to a more generic place
    const FLASH_ERROR_MESSAGE = 'bungle.errorMessage';

    private Registry $registry;
    private AuthorizationCheckerInterface $authChecker;
    private DocumentManager $docManager;
    private RequestStack $reqStack;

    public function __construct(
        Registry $registry,
        AuthorizationCheckerInterface $authChecker,
        DocumentManager $docManager,
        RequestStack $reqStack
    ) {
        $this->registry = $registry;
        $this->authChecker = $authChecker;
        $this->docManager =$docManager;
        $this->reqStack = $reqStack;
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
            $role = self::getTransitionRole(
                $sm->getName(),
                $tr->getName(),
            );
            if ($this->authChecker->isGranted($role)) {
                $r[]=$tr->getName();
            }
        }
        return $r;
    }

    /**
     * Apply specific transition on subject.
     *
     * Handles TransitionException, put the error message into
     * session flash, next page request can display it to user.
     */
    public function applyTransition(object $subject, string $name): void
    {
        $wf = $this->registry->get($subject);
        try {
            $this->doApplyTransition($wf, $subject, $name);
        } catch (TransitionException $e) {
            $this->reqStack
                 ->getCurrentRequest()
                 ->getSession()
                 ->getFlashBag()
                 ->add(self::FLASH_ERROR_MESSAGE, $e->getMessage());
        }
    }

    /**
     * Like applyTransition(), but not handles TransitionException.
     */
    public function applyTransitionRaw(object $subject, string $name): void
    {
        $wf = $this->registry->get($subject);
        $this->doApplyTransition($wf, $subject, $name);
    }

    private function doApplyTransition(WorkflowInterface $wf, $subject, string $name): void
    {
        $wf->apply($subject, $name);
        $this->docManager->persist($subject);
        $this->docManager->flush();
    }

    public static function getTransitionRole(string $workflowName, string $transitionName): string
    {
        return 'ROLE_'.$workflowName.'_'.$transitionName;
    }
}
