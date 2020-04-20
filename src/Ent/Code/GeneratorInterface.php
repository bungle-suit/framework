<?php
declare(strict_types=1);

namespace Bungle\Framework\Ent\Code;

/**
 * Interface do the actual code generate job.
 */
interface GeneratorInterface
{
    /**
     * If generator support the entity object, returns true.
     */
    public function supports(object $entity): bool;

    /**
     * If supports() returns true, CodeGenerator call generate() to generate new code.
     */
    public function generate(object $entity): string;
}
