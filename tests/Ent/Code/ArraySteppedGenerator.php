<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\Code;

use Bungle\Framework\Ent\Code\AbstractSteppedGenerator;

class ArraySteppedGenerator extends AbstractSteppedGenerator
{
    private array $steps;

    /**
     * @param callable[] $steps
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
