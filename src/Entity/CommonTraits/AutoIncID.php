<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity\CommonTraits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Use auto-increased integer as primary id.
 */
trait AutoIncID
{
    /**
     * @ORM\Id
     * @ORM\Column("integer", name="id")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    public function getId(): int
    {
        return $this->id ?? 0;
    }
}
