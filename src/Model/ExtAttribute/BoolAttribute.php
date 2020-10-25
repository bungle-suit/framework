<?php
declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class BoolAttribute extends AbstractAttribute
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $attrs = [
            'label' => $this->getLabel(), 'required' => false,
        ];
        if ($this->getDescription()) {
            $attrs['help'] = $this->getDescription();
        }
        $builder->add(
            $this->getName(),
            CheckBoxType::class,
            $attrs,
        );
    }

    public function createDefault()
    {
        return false;
    }

    public function restoreValue(AttributeInterface $attribute)
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
