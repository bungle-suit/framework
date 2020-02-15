<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine\EventListener;

use Symfony\Component\Workflow\Event\TransitionEvent;
use Bungle\Framework\Exception\TransitionException;
use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\StateMachine\StepContext;

/**
 * Host STTInterfaces, run transition steps on state
 * machine transition.
 *
 * TODO: generate class for each entity class, for better performance.
 *
 * For Entity class `Foo`, assume STT class is `..\STT\FooSTT`.
 */
final class TransitionEventListener
{
    private EntityRegistry $entityRegistry;
    public function __construct(EntityRegistry $entityRegistry)
    {
        $this->entityRegistry = $entityRegistry;
    }

    public function __invoke(TransitionEvent $event): void
    {
        $subject = $event->getSubject();
        $entityClass = \get_class($subject);
        $sttClass = static::getSTTClass($entityClass);
        assert(($sttClass.'::getHighPrefix')() == $this->entityRegistry->getHigh($entityClass));

        $steps = $this->getSteps($subject, $event->getTransition()->getName());
        $ctx = new StepContext($event->getWorkflow(), $event->getTransition());
        foreach ($steps as $step) {
            $msg = call_user_func($step, $subject, $ctx);
            if (is_string($msg)) {
                throw new TransitionException($msg);
            }
        }
    }

    /**
     * Return STT class by entity class name.
     */
    public static function getSTTClass(string $entityClass): string
    {
      assert(strpos($entityClass, 'Entity\\') != -1,
        'Entity class should in "Entity" namespace');
      return str_replace('Entity', 'STT', $entityClass) . 'STT';
    }

    private function getSteps($subject, string $transitionName): array
    {
        $entityClass = \get_class($subject);
        $sttClass = static::getSTTClass($entityClass);
        $sttHigh = ($sttClass.'::getHighPrefix')();
        assert($sttHigh == $this->entityRegistry->getHigh($entityClass));

        $trans = $sttClass::getActionSteps();
        if (!isset($trans[$transitionName])) {
            trigger_error(
                sprintf('StateMachine %s transition "%s" not defined', $sttHigh, $transitionName),
                E_USER_WARNING
            );
            return [];
        }
        return $trans[$transitionName];
    }
}
