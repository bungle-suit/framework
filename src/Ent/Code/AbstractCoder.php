<?php

declare(strict_types=1);

namespace Bungle\Framework\Ent\Code;

/**
 * @template T
 * @implements CoderInterface<T>
 */
abstract class AbstractCoder implements CoderInterface
{
    /**
     * @var array<CoderStepInterface<T>|callable(T, CodeContext): ?string> $steps
     */
    private array $steps;

    /**
     * @param array<CoderStepInterface<T>|callable(T, CodeContext): ?string> $steps
     */
    public function __construct(array $steps)
    {
        $this->steps = $steps;
    }

    function __invoke($entity, CodeContext $context = null): string
    {
        $context = $context ?? new CodeContext();
        CodeSteps::runSteps($this->steps, $entity, $context);

        return strval($context);
    }
}
