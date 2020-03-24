<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

/**
 * Interface SyncToDBInterface, Vina service use it to save
 * entity objects to DB after `save()` and `applyTransition()`.
 */
interface SyncToDBInterface
{
    function syncToDB($entity): void;
}
