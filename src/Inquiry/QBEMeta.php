<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

use Bungle\Framework\Model\HasAttributes;
use Bungle\Framework\Model\HasAttributesInterface;
use LogicException;
use Symfony\Component\PropertyInfo\Type;

/**
 * Describe a QBE value.
 */
class QBEMeta implements HasAttributesInterface
{
    use HasAttributes;

    private string $name;
    private Type $type;
    private string $label;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(string $name, string $label, Type $type, array $options = [])
    {
        if (!$type->isNullable()) {
            throw new LogicException("QBE value must allow null. ($name)");
        }

        $this->name = $name;
        $this->type = $type;
        $this->initAttributes($options);
        $this->label = $label;
    }

    /**
     * Value name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
