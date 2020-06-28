<?php
declare(strict_types=1);

namespace Bungle\Framework\Entity\CommonTraits;

use Doctrine\ORM\Mapping as ORM;

trait NameAble
{
    /**
     * 名称
     *
     * @ORM\Id
     * @ORM\Column()
     */
    protected string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
