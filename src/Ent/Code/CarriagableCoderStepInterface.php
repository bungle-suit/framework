<?php

declare(strict_types=1);

namespace Bungle\Framework\Ent\Code;

/**
 * @template T
 * Step that support carriage.
 *
 * If lower steps run out of code space, Coder call CarriagableCoderStep to generate
 * a new higher code part.
 * @extends CoderStepInterface<T>
 */
interface CarriagableCoderStepInterface extends CoderStepInterface
{
}
