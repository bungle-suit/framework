<?php
declare(strict_types=1);

namespace Bungle\Framework\Ent\Code;

use LogicException;

/**
 * @template T
 */
abstract class AbstractSteppedGenerator implements GeneratorInterface
{
    /**
     * @phpstan-param T $entity
     */
    final public function generate($entity): string
    {
        $ctx = new CodeContext();
        $steps = $this->steps();
        foreach ($steps as $step) {
            $step($entity, $ctx);
        }
        if ($ctx->result === '') {
            $cls = static::class;
            throw new LogicException("No code step set \$result. ($cls)");
        }
        return $ctx->result;
    }

    /**
     * Returns code generate steps, each callback accept two arguments:
     * 1. the current entity
     * 2. CodeContext object.
     *
     * @return array<callable(T, CodeContext): void>
     */
    abstract protected function steps(): array;
}
