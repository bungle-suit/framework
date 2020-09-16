<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

use LogicException;
use Symfony\Component\PropertyInfo\Type;

/**
 * Describe a QBE value.
 */
class QBEMeta
{
    private string $name;
    private Type $type;

    public function __construct(string $name, Type $type)
    {
        if (!$type->isNullable()) {
            throw new LogicException("QBE value must allow null. ($name)");
        }

        $this->name = $name;
        $this->type = $type;
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
}
