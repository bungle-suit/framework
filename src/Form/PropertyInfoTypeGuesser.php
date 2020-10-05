<?php

declare(strict_types=1);

namespace Bungle\Framework\Form;

use DateTime;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\ValueGuess;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

class PropertyInfoTypeGuesser implements FormTypeGuesserInterface
{
    private PropertyInfoExtractorInterface $propertyInfoExtractor;

    public function __construct(PropertyInfoExtractorInterface $propertyInfoExtractor)
    {
        $this->propertyInfoExtractor = $propertyInfoExtractor;
    }

    public function guessType(string $class, string $property): ?TypeGuess
    {
        $types = $this->propertyInfoExtractor->getTypes($class, $property);
        if (0 === count($types)) {
            return null;
        }

        $t = $types[0];

        if ($t->isCollection()) {
            return null;
        }

        switch ($t->getBuiltinType()) {
            case Type::BUILTIN_TYPE_INT:
                return new TypeGuess(IntegerType::class, [], Guess::HIGH_CONFIDENCE);
            case Type::BUILTIN_TYPE_FLOAT:
                return new TypeGuess(NumberType::class, [], Guess::HIGH_CONFIDENCE);
            case Type::BUILTIN_TYPE_BOOL:
                return new TypeGuess(CheckboxType::class, [], Guess::HIGH_CONFIDENCE);
            case Type::BUILTIN_TYPE_OBJECT:
                if ($t->getClassName() === DateTime::class) {
                    return new TypeGuess(
                        DateType::class,
                        ['html5' => true, 'widget' => 'single_text'],
                        Guess::HIGH_CONFIDENCE
                    );
                }
        }

        return null;
    }

    public function guessRequired(string $class, string $property): ?ValueGuess
    {
        $types = $this->propertyInfoExtractor->getTypes($class, $property);
        if (0 === count($types)) {
            return null;
        }

        if ($types[0]->isNullable()) {
            return new ValueGuess(false, Guess::MEDIUM_CONFIDENCE);
        }
        return null;
    }

    public function guessMaxLength(string $class, string $property): ?ValueGuess
    {
        return null;
    }

    public function guessPattern(string $class, string $property): ?ValueGuess
    {
        return null;
    }
}
