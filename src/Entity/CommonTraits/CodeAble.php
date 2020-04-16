<?php
declare(strict_types=1);

namespace Bungle\Framework\Entity\CommonTraits;

trait CodeAble
{
    protected string $code = '';

    public function getCode(): string {
        return $this->code;
    }

    public function setCode(string $code): void {
        $this->code = $code;
    }
}
