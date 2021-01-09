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
     * @var array<CoderStepInterface<T>|callable(T, CodeContext): (string|void)> $steps
     */
    private array $steps;

    /**
     * @param array<CoderStepInterface<T>|callable(T, CodeContext): (string|void)> $steps
     */
    public function __construct(array $steps)
    {
        $this->steps = $steps;
    }

    function __invoke($entity, CodeContext $context = null): string
    {
        $context = $context ?? new CodeContext();
        foreach ($this->steps as $step) {
            $r = $step($entity, $context);
            if (is_string($r)) {
                $context->addSection($r);
            }
        }
        return strval($context);
    }
}
