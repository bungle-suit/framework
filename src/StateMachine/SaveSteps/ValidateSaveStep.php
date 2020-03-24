<?php
declare(strict_types=1);

namespace Bungle\Framework\StateMachine\SaveSteps;

use Bungle\Framework\Entity\CommonTraits\StatefulInterface;
use Bungle\Framework\StateMachine\SaveStepContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Save step to validate the entity.
 *
 * Always use Default Validation group.
 *
 * Set @see ValidateSaveStep::VALIDATED to skip ValidationSaveStep.
 */
class ValidateSaveStep
{
    /**
     * Attribute name that if its value is true, then skip validation skip.
     */
    public const VALIDATED = 'validated';
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function __invoke(StatefulInterface $entity, SaveStepContext $context)
    {
        if ($context->get(self::VALIDATED, false)) {
            return null;
        }

        $errors = $this->validator->validate($entity);
        if ( count($errors)) {
            return (string)$errors;
        }
        return null;
    }
}
