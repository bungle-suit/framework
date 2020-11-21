<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Code;

use Bungle\Framework\Ent\Code\AbstractSteppedGenerator;

/**
 * @template T
 * @extends AbstractSteppedGenerator<T>
 */
class ArraySteppedGenerator extends AbstractSteppedGenerator
{
    /** @phpstan-var (callable(T, \Bungle\Framework\Ent\Code\CodeContext): void)[]  */
    private array $steps;

    /**
     * @param (callable(T, \Bungle\Framework\Ent\Code\CodeContext): void)[] $steps
     */
    public function __construct(array $steps)
    {
        $this->steps = $steps;
    }

    protected function steps(): array
    {
        return $this->steps;
    }

    public function supports(object $entity): bool
    {
        return true;
    }
}
