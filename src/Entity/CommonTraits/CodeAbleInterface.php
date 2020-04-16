<?php
declare(strict_types=1);

namespace Bungle\Framework\Entity\CommonTraits;

interface CodeAbleInterface
{
    public function getCode(): string;
    public function setCode(string $code): void;
}
