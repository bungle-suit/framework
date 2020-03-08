<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity\CommonTraits;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Use auto-increased integer as primary id.
 */
trait AutoIncID
{
    /**
     * Is undef for a new document.
     *
     * @ODM\Id(strategy="INCREMENT")
     */
    protected int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
