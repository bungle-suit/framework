<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

/**
 * State Transition Table interface
 *
 * Bungle use symfony/Workflow as state machine engine,
 * STTInterface defines tractions actions(steps) on
 * object from state A -> B.
 *
 * Use HighPrefix as symfony workflow name.
 *
 * By conversion, implementation classes should end with STT.
 */
interface STTInterface
{
    /**
     * Returns high prefix this STT table work for.
     */
    public static function getHighPrefix(): string;

    /**
     * Returns array, action -> steps.
     *
     * `action` is transition action name, defined as symfony/workflow transition metadata item: `action`.
     *
     * Steps is an array of callbacks executed in order.
     * Step accept two arguments: the object, StepContext.
     * If step function returns non-null string, the transition abort,
     * and raise exception TransitonException with the returned string
     * as message.
     */
    public static function getActionSteps(): array;
}
