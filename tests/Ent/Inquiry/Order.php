<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Inquiry;

class Order
{
    public static function create(string $id, string $name): self
    {
        $r = new self();
        $r->id = $id;
        $r->setName($name);

        return $r;
    }

    private string $id;

    public function getId(): string
    {
        return $this->id;
    }

    protected string $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $v): void
    {
        $this->name = $v;
    }
}
