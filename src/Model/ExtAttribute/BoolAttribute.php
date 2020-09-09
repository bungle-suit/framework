<?php
declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class BoolAttribute implements AttributeDefinitionInterface
{
    private string $label;
    private string $name;
    private string $description;

    public function __construct(string $name, string $label, string $description = '')
    {
        $this->name = $name;
        $this->label = $label;
        $this->description = $description;
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
        $attrs = [
            'label' => $this->getLabel(), 'required' => false,
        ];
        if ($this->description) {
            $attrs['help'] = $this->description;
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

    public function getDescription(): string
    {
        return $this->description;
    }
}
