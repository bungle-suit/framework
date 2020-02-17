<?php
declare(strict_types=1);

namespace Bungle\Framework\Entity\CommonTraits;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Use object id as entity $id field.
 */
trait ObjectID
{
    /** @ODM\Id */
    protected string $id;
  
    public function getId(): string
    {
        return $this->id;
    }
}
