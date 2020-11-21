<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine\MarkingStore;

use LogicException;
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
            throw new LogicException(
                sprintf(
                    'Workspace object "%s" state property "%s" should returns string',
                    get_class($subject),
                    $this->property
                )
            );
        }

        return new Marking([$cur => 1]);
    }

    /**
     * {@inheritdoc}
     * @phpstan-param array<string, mixed> $context
     */
    public function setMarking(object $subject, Marking $marking, array $context = []): void
    {
        $subject->{$this->property} = key($marking->getPlaces());
    }
}
