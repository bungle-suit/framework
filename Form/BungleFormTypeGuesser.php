<?php

declare(strict_types=1);

namespace Bungle\Framework\Form;

use Bungle\Framework\Entity\EntityMetaRepository;
use Bungle\Framework\Entity\EntityRegistry;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * BungleFormTypeGuesser add logic name as label of inner Guesser result.
 */
class BungleFormTypeGuesser implements FormTypeGuesserInterface
{
    private FormTypeGuesserInterface $inner;
    private EntityMetaRepository $entityMetaRepository;
    private EntityRegistry $entityRegistry;

    /**
     * @param $inner, normally should use ValidatorTypeGuesser
     */
    public function __construct(
        FormTypeGuesserInterface $inner,
        EntityMetaRepository $entityMetaRepository,
        EntityRegistry $entityRegistry
    ) {
        $this->inner = $inner;
        $this->entityRegistry = $entityRegistry;
        $this->entityMetaRepository = $entityMetaRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function guessType(string $class, string $property): ?TypeGuess
    {
        $inner = $this->inner->guessType($class, $property);
        if (!$inner) {
            return $inner;
        }

        $high = $this->entityRegistry->getHighSafe($class);
        if (!$high) {
            return $inner;
        }

        $meta = $this->entityMetaRepository->get($high);
        $logicName = $meta->getProperty($property)->logicName;
        $options = $inner->getOptions() + ['label' => $logicName];

        return new TypeGuess($inner->getType(), $options, $inner->getConfidence());
    }

    /**
     * {@inheritdoc}
     */
    public function guessRequired(string $class, string $property)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function guessMaxLength(string $class, string $property)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function guessPattern(string $class, string $property)
    {
        return null;
    }
}
