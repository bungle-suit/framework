<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine\MarkingStore;

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
        $cur = $subject->getState();
        return new Marking([$cur => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function setMarking(object $subject, Marking $marking, array $context = [])
    {
        $subject->setState(key($marking->getPlaces()));
    }
}
