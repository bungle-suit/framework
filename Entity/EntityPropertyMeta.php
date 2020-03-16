<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

final class EntityPropertyMeta
{
    public string $name;
    public string $logicName;

    public function __construct(string $name, string $logicName)
    {
        $this->name = $name;
        $this->logicName = $logicName;
    }
}
