<?php

declare(strict_types=1);

namespace Bungle\Framework\Ent\Code;

/**
 * Generate a new code for an entity instance.
 *
 * Do not confuse with GeneratorInterface/CodeGenerator they are designed
 * for workflow based entities.
 * @template T
 */
interface CoderInterface
{
    /**
     * Auto create $context if null.
     * @param T $entity
     */
    function __invoke($entity, CodeContext $context = null): string;
}
