<?php
declare(strict_types=1);

namespace Bungle\Framework\Form;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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

        if ($types[0]->isCollection()) {
            return new TypeGuess(ChoiceType::class, [], Guess::LOW_CONFIDENCE);
        }

        switch ($types[0]->getBuiltinType()) {
            case Type::BUILTIN_TYPE_STRING:
                return new TypeGuess(TextType::class, [], Guess::LOW_CONFIDENCE);
            case Type::BUILTIN_TYPE_INT:
                return new TypeGuess(IntegerType::class, [], Guess::LOW_CONFIDENCE);
            case Type::BUILTIN_TYPE_FLOAT:
                return new TypeGuess(NumberType::class, [], Guess::LOW_CONFIDENCE);
            case Type::BUILTIN_TYPE_BOOL:
                return new TypeGuess(CheckboxType::class, [], Guess::LOW_CONFIDENCE);
        }

        return null;
    }

    public function guessRequired(string $class, string $property): ?ValueGuess
    {
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
