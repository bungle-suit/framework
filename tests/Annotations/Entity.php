<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Annotations;

use Bungle\Framework\Annotation\High;

/**
 * @High("ent")
 */
class Entity
{
    public string $id;

    public int $count;

    // Use name as logic name if no LogicName annotation defined
    public string $name;

    private string $includePrivate;

    protected string $includeProtected;

    public static string $ignoreStatic;
}
