<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine\Events;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Symfony\Contracts\EventDispatcher\Event;

class SaveEvent extends Event
{
    private StatefulInterface $subject;
    private array $attrs;

    public function __construct(StatefulInterface $subject, array $attrs)
    {
        $this->subject = $subject;
        $this->attrs = $attrs;
    }

    public function getSubject(): StatefulInterface
    {
        return $this->subject;
    }

    public function getAttrs(): array
    {
        return $this->attrs;
    }

}
