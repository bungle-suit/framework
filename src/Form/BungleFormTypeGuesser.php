<?php

declare(strict_types=1);

namespace Bungle\Framework\Form;

use Bungle\Framework\Entity\EntityMetaRepository;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\ValueGuess;

/**
 * BungleFormTypeGuesser add logic name as label of inner Guesser result.
 */
class BungleFormTypeGuesser implements FormTypeGuesserInterface
{
    private FormTypeGuesserInterface $inner;
    private EntityMetaRepository $entityMetaRepository;

    /**
     * @param $inner, normally should use ValidatorTypeGuesser
     */
    public function __construct(
        FormTypeGuesserInterface $inner,
        EntityMetaRepository $entityMetaRepository
    ) {
        $this->inner = $inner;
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

        $meta = $this->entityMetaRepository->get($class);
        $logicName = $meta->getProperty($property)->logicName;
        $options = ['label' => $logicName];
        if (TextType::class == $inner->getType()) {
            // If not set, TextType convert empty string to null,
            // I think null string is not allowed, always can use empty string.
            $options['empty_data'] = '';
        } elseif (DateTimeType::class == $inner->getType()) {
            $options['widget'] = 'single_text';
        }
        $options = $inner->getOptions() + $options;

        return new TypeGuess($inner->getType(), $options, $inner->getConfidence());
    }

    /**
     * {@inheritdoc}
     */
    public function guessRequired(string $class, string $property): ?ValueGuess
    {
        return $this->inner->guessRequired($class, $property);
    }

    /**
     * {@inheritdoc}
     */
    public function guessMaxLength(string $class, string $property): ?ValueGuess
    {
        return $this->inner->guessMaxLength($class, $property);
    }

    /**
     * {@inheritdoc}
     */
    public function guessPattern(string $class, string $property): ?ValueGuess
    {
        return $this->inner->guessPattern($class, $property);
    }
}
