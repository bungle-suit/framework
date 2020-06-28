<?php
declare(strict_types=1);

namespace Bungle\Framework\Entity\CommonTraits;

use Doctrine\ORM\Mapping as ORM;

trait CodeAble
{
    /**
     * 编号
     *
     * @ORM\Column(type="string")
     * @ORM\Index(unique=true)
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
