<?php
declare(strict_types=1);

namespace Bungle\Framework\Ent\Code;

use LogicException;

class CodeGenerator
{
    private array $generators;

    /**
     * @param GeneratorInterface[] $generators
     */
    public function __construct(array $generators)
    {
        $this->generators = $generators;
    }

    /**
     * Return generated code for $entity object.
     */
    public function generate(object $entity): string
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($entity)) {
                return $generator->generate($entity);
            }
        }

        $cls = get_class($entity);
        throw new LogicException("No code generator support class '$cls'");
    }
}
