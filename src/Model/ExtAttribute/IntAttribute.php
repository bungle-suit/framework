<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class IntAttribute extends AbstractAttribute
{
    public function getFormType(): string
    {
        return IntegerType::class;
    }

    public function getFormOption(): array
    {
        return [];
    }

    public function createDefault(): int
    {
        return 0;
    }

    public function restoreValue(AttributeInterface $attribute)
    {
        return $attribute->asInt();
    }

    public function saveValue(AttributeInterface $attribute, $value): void
    {
        $attribute->setInt($value);
    }
}
