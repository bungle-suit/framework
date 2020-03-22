<?php

declare(strict_types=1);

namespace Bungle\Framework\Inquiry\Steps;

use Bungle\Framework\Inquiry\StepContext;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Step set query conditions by field value types.
 *
 * 1. Ignore(do not add) if field value is falsy.
 * 2. If value is string, add a regex (contains) condition
 * 3. Other value add equals condition.
 */
class AutoFieldConditions
{
    /**
     * @var array
     */
    private array $fields;

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function __invoke(StepContext $context): void
    {
        $build = $context->getBuilder();
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($this->fields as $field) {
            $v = $accessor->getValue($context->getParams()->qbe, $field);
            if (!boolval($v)) {
                continue;
            }

            if (is_string($v)) {
                $build->field($field)->equals(new Regex($v));
            } else {
                $build->field($field)->equals($v);
            }
        }
    }
}
