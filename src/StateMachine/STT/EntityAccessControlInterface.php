<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine\STT;

interface EntityAccessControlInterface
{
    /**
     * @return callable[] callbacks accept one argument the object, returns true/false.
     * If any callback returns false, then current user can not access/read the object.
     */
    public function canAccess(): array;
}
