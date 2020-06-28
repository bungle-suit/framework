<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;

class EmptySyncToDB implements SyncToDBInterface
{
    public function syncToDB(StatefulInterface $entity): void
    {
    }
}
