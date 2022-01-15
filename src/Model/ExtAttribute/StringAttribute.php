<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

use Symfony\Component\Form\Extension\Core\Type\TextType;

class StringAttribute extends AbstractAttribute
{
    public function createDefault(): string
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

    public function getFormType(): string
    {
        return TextType::class;
    }

    public function getFormOption(): array
    {
        return [
            'empty_data' => '',
        ];
    }
}
