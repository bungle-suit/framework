<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Symfony\Contracts\EventDispatcher\Event;

class HaveSaveActionResolveEvent extends Event
{
    private $subject;
    private ?bool $enabled;

    public function __construct($subject = null)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setHaveSaveAction()
    {
        $this->enabled = true;
    }

    public function isHaveSaveAction(): bool
    {
        return $this->enabled ?? false;
    }
}
