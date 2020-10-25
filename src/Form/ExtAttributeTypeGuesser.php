<?php

declare(strict_types=1);

namespace Bungle\Framework\Form;

use Bungle\Framework\Model\ExtAttribute\AttributeDefinitionInterface;
use Bungle\Framework\Model\ExtAttribute\AttributeSetDefinition;
use Bungle\Framework\Model\ExtAttribute\DataMapper\AbstractNormalizedAttributes;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\ValueGuess;

class ExtAttributeTypeGuesser implements FormTypeGuesserInterface
{
    public function guessType(string $class, string $property): ?TypeGuess
    {
        $def = self::supports($class, $property);
        if ($def === null) {
            return null;
        }

        $options = [
            'required' => false,
            'label' => $def->getLabel(),
        ];
        if ($def->getDescription()) {
            $options['help'] = $def->getDescription();
        }
        $options = array_merge($options, $def->getFormOption());

        return new TypeGuess($def->getFormType(), $options, TypeGuess::VERY_HIGH_CONFIDENCE);
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

    /**
     * Returns true if $class is AbstractNormalizedAttributes::class sub class.
     */
    public static function supports(string $class, string $property): ?AttributeDefinitionInterface
    {
        if (!is_subclass_of($class, AbstractNormalizedAttributes::class)) {
            return null;
        }

        /** @var AttributeSetDefinition $def */
        $def = ([$class, 'getDefinition'])();

        return $def->getAttributeDefinitions()[$property] ?? null;
    }
}
