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
     * @return string|void if returns string, will append to code section.
     */
    function __invoke($entity, CodeContext $context);
}
