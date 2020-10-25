<?php

declare(strict_types=1);

namespace Bungle\Framework\Form\Type;

use Bungle\Framework\Form\DataMapper\AttributeSetNormalizer;
use Bungle\Framework\Model\ExtAttribute\AttributeSetDefinition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BaseExtAttributeSetType extends AbstractType
{
    private string $normalizedAttributeSetClass;

    /**
     * @phpstan-param class-string<\Bungle\Framework\Model\ExtAttribute\DataMapper\AbstractNormalizedAttributes>
     */
    public function __construct(string $extAttributeSetType)
    {
        $this->normalizedAttributeSetClass = $extAttributeSetType;
    }

    final public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(
            new AttributeSetNormalizer($this->normalizedAttributeSetClass)
        );

        $this->doBuildForm($builder, $options);
    }

    /**
     * Add attributes to the form.
     *
     * Override to customize field types.
     * @noinspection PhpUnusedParameterInspection
     */
    protected function doBuildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var AttributeSetDefinition $def */
        $def = ([$this->normalizedAttributeSetClass, 'getDefinition'])();
        foreach ($def->getAttributeDefinitions() as $field) {
            $builder->add($field->getName());
        }
    }
}
