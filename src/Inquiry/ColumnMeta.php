<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

use Symfony\Component\PropertyInfo\Type;

/**
 * Metadata of a column of query result dataset
 */
class ColumnMeta
{
    private string $path;
    private string $label;
    private Type $type;

    public function __construct(string $path, string $label, Type $type)
    {
        $this->path = $path;
        $this->label = $label;
        $this->type = $type;
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
