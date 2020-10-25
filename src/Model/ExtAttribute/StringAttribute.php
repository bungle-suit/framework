<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

use Symfony\Component\Form\FormBuilderInterface;

class StringAttribute extends AbstractAttribute
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
    }

    public function createDefault()
    {
        return '';
    }

    public function restoreValue(AttributeInterface $attribute)
    {
        return $attribute->getValue();
    }

    public function saveValue(AttributeInterface $attribute, $value): void
    {
        $attribute->setValue($value);
    }
}
