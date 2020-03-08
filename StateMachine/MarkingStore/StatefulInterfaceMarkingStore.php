<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine\MarkingStore;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface;

/**
 * Stores the marking using StatefulInterface get/set
 * state methods.
 *
 * This store works only for "single state".
 */
class StatefulInterfaceMarkingStore implements MarkingStoreInterface
{
    /**
     * {@inheritdoc}
     */
    public function getMarking(object $subject): Marking
    {
        assert($subject instanceof StatefulInterface, get_class($subject).' not implement StatefulInterface');

        $cur = $subject->getState();

        return new Marking([$cur => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function setMarking(object $subject, Marking $marking, array $context = [])
    {
        assert($subject instanceof StatefulInterface, get_class($subject).' not implement StatefulInterface');

        $subject->setState(key($marking->getPlaces()));
    }
}
