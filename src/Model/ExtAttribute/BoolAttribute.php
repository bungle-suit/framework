<?php
declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class BoolAttribute implements AttributeDefinitionInterface
{
    private string $label;
    private string $name;

    public function __construct(string $name, string $label)
    {
        $this->name = $name;
        $this->label = $label;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add($this->getName(), CheckBoxType::class, ['label' => $this->getLabel()]);
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
}
