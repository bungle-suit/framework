<?php
declare(strict_types=1);

namespace Bungle\Framework\Ent\Code;

use LogicException;

abstract class AbstractSteppedGenerator implements GeneratorInterface
{
    final public function generate(object $entity): string
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
     * @return callable[]
     */
    abstract protected function steps(): array;
}
