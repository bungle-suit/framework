<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * SyncToDBInterface ODM version
 */
class ODMSyncToDB implements SyncToDBInterface
{
    /**
     * @var DocumentManager
     */
    private DocumentManager $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    function syncToDB(StatefulInterface $entity): void
    {
        $this->dm->persist($entity);
        $this->dm->flush();
    }
}
