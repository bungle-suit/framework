<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry\Steps;

use Bungle\Framework\Inquiry\StepContext;

/**
 * Step to set fields of query.
 */
class SetFields
{
    /**
     * @var array|string[]
     */
    private array $fields;

    /**
     * @param string[] $fields
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function __invoke(StepContext $context)
    {
        $context->getBuilder()->select($this->fields);
    }
}
