<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine\Steps;

use Bungle\Framework\Ent\Code\CodeGenerator;
use Bungle\Framework\Entity\CommonTraits\CodeAbleInterface;
use Bungle\Framework\StateMachine\StepContext;

/**
 * StateMachine step to set code of CodeAble entity by using
 * CodeGenerator.
 */
class SetCodeStep
{
    private CodeGenerator $codeGenerator;

    public function __construct(CodeGenerator $codeGenerator)
    {
        $this->codeGenerator = $codeGenerator;
    }

    public function __invoke(CodeAbleInterface $subject, StepContext $context): void
    {
        $code = $this->codeGenerator->generate($subject);
        $subject->setCode($code);
    }
}
