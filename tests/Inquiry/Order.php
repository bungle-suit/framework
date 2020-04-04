<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document
 */
class Order
{
    public static function create(string $id, string $name): self {
        $r = new self();
        $r->id = $id;
        $r->setName($name);

        return $r;
    }

    /**
     * @ODM\Id(strategy="NONE")
     */
    private string $id;

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @ODM\Field(type="string")
     */
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
