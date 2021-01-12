<?php

declare(strict_types=1);

namespace Bungle\Framework\Ent\Code;

/**
 * @template T
 */
interface CoderStepInterface
{
    /**
     * @param T $entity
     * If returns string, will append to code section.
     */
    function __invoke($entity, CodeContext $context): ?string;
}
