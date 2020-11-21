<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

use Bungle\Framework\Model\HasAttributes;
use Bungle\Framework\Model\HasAttributesInterface;
use Symfony\Component\PropertyInfo\Type;

/**
 * Metadata of a column of query result dataset
 */
class ColumnMeta implements HasAttributesInterface
{
    use HasAttributes;

    private string $path;
    private string $label;
    private Type $type;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(string $path, string $label, Type $type, array $options = [])
    {
        $this->path = $path;
        $this->label = $label;
        $this->type = $type;
        $this->initAttributes($options);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getType(): Type
    {
        return $this->type;
    }
}
