<?php
declare(strict_types=1);

namespace Bungle\Framework\Entity\CommonTraits;

trait NameAble
{
    protected string $name = '';

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }
}
