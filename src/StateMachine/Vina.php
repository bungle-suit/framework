<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Bungle\Framework\StateMachine\Events\SaveEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Workflow\Exception\TransitionException;
use Symfony\Component\Workflow\Registry;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Vina is a service help us to handle StateMachine
 * common operations.
 *
 * The name Vina considered to be the name of AI
 * Assistant.
 */
class Vina
{
    const FLASH_ERROR_MESSAGE = 'bungle.errorMessage';

    private Registry $registry;
    private AuthorizationCheckerInterface $authChecker;
    private RequestStack $reqStack;
    private ?EventDispatcherInterface $dispatcher;
    private SyncToDBInterface $syncToDB;

    public function __construct(
        Registry $registry,
        AuthorizationCheckerInterface $authChecker,
        RequestStack $reqStack,
        EventDispatcherInterface $dispatcher = null,
        SyncToDBInterface $syncToDB = null
    ) {
        $this->registry = $registry;
        $this->authChecker = $authChecker;
        $this->reqStack = $reqStack;
        $this->dispatcher = $dispatcher;
        $this->syncToDB = $syncToDB ?? new EmptySyncToDB();
    }

    /**
     * Return title of subject current state.
     */
    public function getCurrentStateTitle(StatefulInterface $subject): string
    {
        $titles = $this->getStateTitles($subject);

        return $titles[$subject->getState()] ?? $subject->getState();
    }

    /**
     * Returns associated array of transition name -> title
     * for StateMachine attached with $subject.
     */
    public function getTransitionTitles(StatefulInterface $subject): array
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
     * for StateMachine attached with $subject.
     */
    public function getStateTitles(StatefulInterface $subject): array
    {
        // TODO: cache result by subject classname
        $sm = $this->registry->get($subject);
        $store = $sm->getMetadataStore();
        $r = [];
        foreach ($sm->getDefinition()->getPlaces() as $place) {
            $meta = $store->getPlaceMetadata($place);
            $r[$place] = $meta['title'] ?? (
                StatefulInterface::INITIAL_STATE == $place ? '未保存' : $place
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
    public function getPossibleTransitions(StatefulInterface $subject): array
    {
        $sm = $this->registry->get($subject);
        $trans = $sm->getEnabledTransitions($subject);
        $r = [];
        foreach ($trans as $tr) {
            $role = self::getTransitionRole(
                $sm->getName(),
                $tr->getName(),
            );
            if ($this->authChecker->isGranted($role)) {
                $r[] = $tr->getName();
            }
        }

        return $r;
    }

    /**
     * Apply specific transition on subject.
     *
     * Handles TransitionException, put the error message into
     * session flash, next page request can display it to user.
     *
     * If succeed, $subject synced to DB.
     * @param array $attrs initial attrs of StepContext.
     */
    public function applyTransition(StatefulInterface $subject, string $name, array $attrs = []): void
    {
        $wf = $this->registry->get($subject);
        try {
            $wf->apply($subject, $name, $attrs);
            $this->syncToDB->syncToDB($subject);
        } catch (TransitionException $e) {
            $request = $this->reqStack->getCurrentRequest();
            if (!$request) {
                throw $e;
            }
            $request
                ->getSession()
                ->getFlashBag()
                ->add(self::FLASH_ERROR_MESSAGE, $e->getMessage());
        }
    }

    /**
     * Like applyTransition(), but not handles TransitionException.
     *
     * $subject not sync to db.
     * @param array $attrs initial attrs of StepContext.
     */
    public function applyTransitionRaw(StatefulInterface $subject, string $name, array $attrs = []): void
    {
        $wf = $this->registry->get($subject);
        $wf->apply($subject, $name);
    }

    /**
     * Returns auth role for specific workflow/transaction.
     *
     * Note: use different role prefix ('R_') than symfony default ('ROLE_') for shorter
     * role names. It should work without problem, some rules need to consider:
     *
     * 1. symfony builtin role names, such as 'ROLE_USER' will break.
     * 1. 3rd party lib breaks.
     *
     * Reconfigure symfony security voter support prefix ('ROLE_'), if the feature not
     *    used, its okay. If depends on the feature, override voter container settings by:
     *
     * security.access.simple_role_voter:
     *     public: true
     *     class: Symfony\Component\Security\Core\Authorization\Voter\RoleVoter
     *     arguments: ['R_']
     */
    public static function getTransitionRole(string $workflowName, string $transitionName): string
    {
        return 'R_'.$workflowName.'_'.$transitionName;
    }

    /**
     * Execute STT save steps. If succeed, $subject synced to DB.
     * @param array $attrs initial attribute for StepContext.
     */
    public function save(StatefulInterface $subject, array $attrs = []): void
    {
        $high = $this->registry->get($subject)->getName();
        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new SaveEvent($subject, $attrs), "vina.$high.save");
        }

        $this->syncToDB->syncToDB($subject);
    }

    /**
     * Returns true if $subject currently allows edit.
     *
     * NOTE: does not consider role of current user.
     */
    public function haveSaveAction(StatefulInterface $subject): bool
    {
        if (!$this->dispatcher) {
            return false;
        }

        $high = $this->registry->get($subject)->getName();
        $e = new HaveSaveActionResolveEvent($subject);
        $this->dispatcher->dispatch($e, "vina.$high.have_save_action");

        return $e->isHaveSaveAction();
    }

    /**
     * Returns true if $subject currently allows edit and current user has related roles.
     *
     * If current user can start any transition on $subject, means has related roles.
     */
    public function canSave(StatefulInterface $subject): bool
    {
        if (!$this->haveSaveAction($subject)) {
            return false;
        }

        return boolval($this->getPossibleTransitions($subject));
    }
}
