<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry\Steps;

use Bungle\Framework\Inquiry\StepContext;

/**
 * If qbe value trusy, add a EQ condition.
 *
 * It is especially useful, to strict compare string value,
 * AutoFieldConditions step use regex to compare strings.
 */
class FieldEqual
{
    private string $field;

    /**
     * @param string $field field name
     */
    public function __construct(string $field)
    {
        $this->field = $field;
    }

    public function __invoke(StepContext $context): void
    {
        $v = $context->getParams()->qbe->{$this->field};
        if ($v) {
            $context->getBuilder()->field($this->field)->equals($v);
        }
    }
}
