<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity\CommonTraits;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Use auto-increased integer as primary id.
 */
trait AutoIncID
{
    // auto increment id also need to initialized, or ODM will trigger
    // $id must not be accessed before initialization
    /**
     * Is undef for a new document.
     *
     * @ODM\Id(strategy="INCREMENT", type="int")
     */
    protected int $id;

    public function getId(): int
    {
        return $this->id ?? 0;
    }
}
