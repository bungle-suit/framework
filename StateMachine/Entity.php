<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use PHPUnit\Framework\TestCase;

/**
 * Base Entity class.
 *
 * Note: Entity class is convenience base class, entity
 * do not required to be `Entity` sub class.
 */
abstract class Entity
{
    public const INITIAL_STATE = 'initial';

    public string $id = '';

    // Stores current state, we should use 'initial' as the first
    // state.
    public string $state = Entity::INITIAL_STATE;
}
