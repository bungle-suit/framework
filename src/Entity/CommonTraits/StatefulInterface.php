<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity\CommonTraits;

/**
 * StatefulInterface entity tracks entity current state,
 * almost all Bungle Framework entity should implement
 * StatefulInterface.
 *
 * StatefulInterface Entity must give `High` name by
 * HighAnnotation.
 *
 * StatefulInterface also works with StateMachine by
 * using STT class.
 */
interface StatefulInterface
{
    public const INITIAL_STATE = 'initial';

    public function getState(): string;

    public function setState(string $v): void;
}
