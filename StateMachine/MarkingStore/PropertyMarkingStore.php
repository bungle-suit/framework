<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine\MarkingStore;

use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface;

/**
 * PropertyMarkingStore stores the marking in a subject object's property.
 *
 * This store works only for "single state".
 */
class PropertyMarkingStore implements MarkingStoreInterface
{
    private string $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMarking(object $subject): Marking
    {
        $cur = $subject->{$this->property};
        if (!is_string($cur)) {
            assert(false, sprintf(
                "Workspace object '%s' state property '%s' should returns string",
                \get_class($subject),
                $this->property
            ));
        }
        return new Marking([$cur => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function setMarking(object $subject, Marking $marking, array $context = [])
    {
        $subject->{$this->property} = key($marking->getPlaces());
    }
}
