<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Bungle\Framework\Traits\HasAttributes;
use Bungle\Framework\Traits\HasAttributesInterface;

/**
 * Context for save steps.
 */
final class SaveStepContext implements HasAttributesInterface
{
    use HasAttributes;

    /**
     * @param array $attrs initial attrs for SaveStepContext.
     */
    public function __construct(array $attrs = [])
    {
        if ($attrs) {
           $this->attributes = $attrs;
        }
    }
}
