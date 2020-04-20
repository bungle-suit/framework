<?php
declare(strict_types=1);

namespace Bungle\Framework\Entity\CommonTraits;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

trait CodeAble
{
    /**
     * Is undef for a new document.
     *
     * @ODM\Field(type="string")
     * @ODM\Index(unique=true)
     */
    protected string $code = '';

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }
}
