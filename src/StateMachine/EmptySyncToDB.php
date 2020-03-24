<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

class EmptySyncToDB implements SyncToDBInterface
{
    function syncToDB($entity): void
    {
    }
}
