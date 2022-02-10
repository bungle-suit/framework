<?php
declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class BoolAttribute extends AbstractAttribute
{
    public function createDefault(): bool
    {
        return false;
    }

    public function restoreValue(AttributeInterface $attribute): bool
    {
        return $attribute->asBool();
    }

    public function saveValue(AttributeInterface $attribute, $value): void
    {
        $attribute->setBool($value);
    }

    public function getFormType(): string
    {
        return CheckboxType::class;
    }

    public function getFormOption(): array
    {
        return [];
    }
}
