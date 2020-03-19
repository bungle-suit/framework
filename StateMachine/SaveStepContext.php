<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Bungle\Framework\Traits\Attributes;
use Bungle\Framework\Traits\HasAttributesInterface;

/**
 * Context for save steps.
 */
final class SaveStepContext implements HasAttributesInterface
{
    use Attributes;
}
