<?php
declare(strict_types=1);

namespace Bungle\Framework\Exception;

/**
 * It is exception raised during execute State Machine transition
 * steps.
 *
 * Derive from ValidationException, because most of error message
 * returned by transition step is invalid message.
 *
 * Catch ValidationException simplifies exception handling.
 */
class TransitionException extends ValidationException
{
}
