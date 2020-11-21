<?php

declare(strict_types=1);

namespace Bungle\Framework\StateMachine;

use Bungle\Framework\Model\HasAttributes;
use Bungle\Framework\Model\HasAttributesInterface;

/**
 * Context for save steps.
 */
final class SaveStepContext implements HasAttributesInterface
{
    use HasAttributes;

    /**
     * @param array<string, mixed> $attrs initial attrs for SaveStepContext.
     */
    public function __construct(array $attrs = [])
    {
        if ($attrs) {
           $this->attributes = $attrs;
        }
    }
}
