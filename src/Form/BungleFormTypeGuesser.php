<?php

declare(strict_types=1);

namespace Bungle\Framework\Form;

use Bungle\Framework\Entity\EntityRegistry;
use Psr\Log\LoggerInterface;
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
    private EntityRegistry $entityRegistry;
    private ?LoggerInterface $logger;

    /**
     * @param $inner, normally should use ValidatorTypeGuesser
     */
    public function __construct(
        FormTypeGuesserInterface $inner,
        EntityRegistry $entityRegistry,
        LoggerInterface $logger = null
    ) {
        $this->inner = $inner;
        $this->entityRegistry = $entityRegistry;
        $this->logger = $logger;
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

        $meta = $this->entityRegistry->getEntityMeta($class);
        $logicName = $meta->getProperty($property)->logicName;
        if (null !== $this->logger) {
            $this->logger->debug("guess label for $class property $property: $logicName");
        }
        $options = ['label' => $logicName];
        if (TextType::class == $inner->getType()) {
            // If not set, TextType convert empty string to null,
            // I think null string is not allowed, always can use empty string.
            $options['empty_data'] = '';
        } elseif (DateTimeType::class == $inner->getType()) {
            $options['widget'] = 'single_text';
        }
        $options = array_merge($inner->getOptions(), $options);

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
