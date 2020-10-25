<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

abstract class AbstractAttribute implements AttributeDefinitionInterface
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
