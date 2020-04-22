<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine\Steps;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Bungle\Framework\StateMachine\StepContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * StateMachine step to validate the entity.
 *
 * Use validation group Default and current transition name.
 *
 * Set @see ValidateStep::VALIDATED to skip ValidationSaveStep.
 */
class ValidateStep
{
    public const VALIDATED = 'validated';
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function __invoke(StatefulInterface $entity, StepContext $context): ?string
    {
        if ($context->get(self::VALIDATED, false)) {
            return null;
        }

        $act = $context->getTransitionName();
        $errors = $this->validator->validate($entity, null, ['Default', $act]);
        if (count($errors)) {
            return (string)$errors;
        }
        return null;
    }
}
