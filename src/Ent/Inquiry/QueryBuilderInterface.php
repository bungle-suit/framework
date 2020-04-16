<?php

declare(strict_types=1);

namespace Bungle\Framework\Ent\Inquiry;

interface QueryBuilderInterface
{
    /**
     * Returns an array of build steps.
     *
     * Build step is a callable object that accept
     * one argument $StepContext.
     */
    public function steps(): array;
}
