<?php

declare(strict_types=1);

namespace Bungle\Framework\Ent\Code;

use LogicException;

/**
 * Coder step raise this exception, if it run out of code space.
 * Coder run nearest CarriagableCoderStepInterface to generate a new
 * coder space.
 */
class CoderOverflowException extends LogicException
{
}
