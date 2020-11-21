<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Bungle\Framework\StateMachine\STTLocator\STTLocatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Workflow\Exception\TransitionException;
use Symfony\Component\Workflow\Registry;

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
    private SyncToDBInterface $syncToDB;
    /** @phpstan-var STTLocatorInterface */
    private STTLocatorInterface $sttLocator;

    /**
     * @phpstan-param STTLocatorInterface $sttLocator
     */
    public function __construct(
        Registry $registry,
        AuthorizationCheckerInterface $authChecker,
        RequestStack $reqStack,
        STTLocatorInterface $sttLocator,
        SyncToDBInterface $syncToDB = null
    ) {
        $this->registry = $registry;
        $this->authChecker = $authChecker;
        $this->reqStack = $reqStack;
        $this->syncToDB = $syncToDB ?? new EmptySyncToDB();
        $this->sttLocator = $sttLocator;
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
     *
     * @return array<string, string>
     */
    public function getTransitionTitles(StatefulInterface $subject): array
    {
        $sm = $this->registry->get($subject);
        $store = $sm->getMetadataStore();
        $r = [];
        foreach ($sm->getDefinition()->getTransitions() as $trans) {
            $meta = $store->getTransitionMetadata($trans);
            /** @var string $transName */
            $transName = $trans->getName();
            $r[$transName] = $meta['title'] ?? $trans->getName();
        }

        return $r;
    }

    /**
     * Return associated array of state/place name -> title
     * for StateMachine attached with $subject.
     * @return array<string, string>
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
     * @return string[]
     */
    public function getPossibleTransitions(StatefulInterface $subject): array
    {
        $sm = $this->registry->get($subject);
        $trans = $sm->getEnabledTransitions($subject);
        $r = [];
        foreach ($trans as $tr) {
            /** @var string $transitionName */
            $transitionName = $tr->getName();
            $role = self::getTransitionRole(
                $sm->getName(),
                $transitionName,
            );
            if ($this->authChecker->isGranted($role)) {
                $r[] = $transitionName;
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
     * @param array<string, mixed> $attrs initial attrs of StepContext.
     */
    public function applyTransition(
        StatefulInterface $subject,
        string $name,
        array $attrs = []
    ): void {
        $wf = $this->registry->get($subject);
        try {
            $wf->apply($subject, $name, $attrs);
            $this->syncToDB->syncToDB($subject);
        } catch (TransitionException $e) {
            $request = $this->reqStack->getCurrentRequest();
            if (!$request) {
                throw $e;
            }

            $session = $request->getSession();
            assert($session instanceof Session);
            $session
                ->getFlashBag()
                ->add(self::FLASH_ERROR_MESSAGE, $e->getMessage());
        }
    }

    /**
     * Like applyTransition(), but not handles TransitionException.
     *
     * $subject not sync to db.
     * @param array<string, mixed> $attrs initial attrs of StepContext.
     */
    public function applyTransitionRaw(
        StatefulInterface $subject,
        string $name,
        array $attrs = []
    ): void {
        $wf = $this->registry->get($subject);
        $wf->apply($subject, $name, $attrs);
    }

    public static function getTransitionRole(string $workflowName, string $transitionName): string
    {
        return 'ROLE_'.$workflowName.'_'.$transitionName;
    }

    /**
     * @template T
     * Execute STT save steps. If succeed, $subject synced to DB.
     * @param array<string, mixed> $attrs initial attribute for StepContext.
     * @phpstan-param T&StatefulInterface $subject
     */
    public function save(StatefulInterface $subject, array $attrs = []): void
    {
        $stt = $this->sttLocator->getSTTForClass(get_class($subject));
        $stt->save($subject, $attrs);

        $this->syncToDB->syncToDB($subject);
    }

    /**
     * @template T
     * Returns true if $subject currently allows edit.
     *
     * NOTE: does not consider role of current user.
     * @phpstan-param T&StatefulInterface $subject
     */
    public function haveSaveAction(StatefulInterface $subject): bool
    {
        $stt = $this->sttLocator->getSTTForClass(get_class($subject));

        return $stt->canSave($subject);
    }

    /**
     * @template T
     * Returns true if $subject currently allows edit and current user has related roles.
     *
     * If current user can start any transition on $subject, means has related roles.
     * @phpstan-param T&StatefulInterface $subject
     */
    public function canSave(StatefulInterface $subject): bool
    {
        if (!$this->haveSaveAction($subject)) {
            return false;
        }

        return boolval($this->getPossibleTransitions($subject));
    }

    /**
     * @template T
     * Create new instance of specific entity class.
     * @phpstan-param class-string<T> $entityClass
     */
    public function createNew(string $entityClass): StatefulInterface
    {
        $stt = $this->sttLocator->getSTTForClass($entityClass);

        return $stt->createNew();
    }
}
