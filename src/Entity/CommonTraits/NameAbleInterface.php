<?php
declare(strict_types=1);

namespace Bungle\Framework\Entity\CommonTraits;

/**
 * Entity class has name property
 */
interface NameAbleInterface
{
    public function getName(): string;
    public function setName(string $name): void;
}
