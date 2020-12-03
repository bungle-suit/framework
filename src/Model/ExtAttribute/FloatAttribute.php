<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

use Symfony\Component\Form\Extension\Core\Type\NumberType;

class FloatAttribute extends AbstractAttribute
{
    public function getFormType(): string
    {
        return NumberType::class;
    }

    public function getFormOption(): array
    {
        return [];
    }

    public function createDefault()
    {
        return 0.0;
    }

    public function restoreValue(AttributeInterface $attribute)
    {
        return $attribute->asFloat();
    }

    public function saveValue(AttributeInterface $attribute, $value): void
    {
        $attribute->setFloat($value);
    }
}
