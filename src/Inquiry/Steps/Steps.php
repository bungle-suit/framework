<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry\Steps;

use Bungle\Framework\Inquiry\QueryParams;
use Bungle\Framework\Inquiry\StepContext;
use MongoDB\BSON\Regex;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Common inquiry search steps.
 */
class Steps
{
    /**
     * setSort method callable.
     */
    const setSort = self::class.'::setSort';

    /**
     * Apply sort settings of @see QueryParams::sort
     */
    public static function setSort(StepContext $context): void
    {
        $sort = $context->getParams()->sort;
        if ($sort && !$context->isBuildForCount()) {
            $context->getBuilder()->sort($sort[0], $sort[1] ? 1 : -1);
        }
    }

    /**
     * Returns a search step set result fields.
     * @param string[] $fields
     */
    public static function setFields(array $fields): Callable
    {
        return fn (StepContext $ctx) => $ctx->getBuilder()->select($fields);
    }

    /**
     * Returns search step set paging info.
     */
    public static function setPaging(int $recsPerPage = 25): Callable
    {
        return function (StepContext $context) use ($recsPerPage):void {
            if ($context->isBuildForCount()) {
                return;
            }

            $context->getBuilder()->skip($recsPerPage * $context->getParams()->pageNo);
            $context->getBuilder()->limit($recsPerPage);
        };
    }

    /**
     * Return step set query conditions by field value types.
     *
     * 1. Ignore(do not add) if field value is falsy.
     * 2. If value is string, add a regex (contains) condition
     * 3. Other value add equals condition.
     *
     * @param string[] $fields
     */
    public static function autoFieldCondition(array $fields): Callable
    {
        return function (StepContext $context) use ($fields): void {
            $build = $context->getBuilder();
            $accessor = PropertyAccess::createPropertyAccessor();
            foreach ($fields as $field) {
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
        };
    }

    /**
     * Returns step add EQ condition. If qbe value trusy, add a EQ condition.
     *
     * It is especially useful, to strict compare string value,
     * @see self::autoFieldConditions step use regex to compare strings.
     */
    public static function fieldEqual(string $field): Callable
    {
        return function (StepContext $context) use ($field): void {
            $v = $context->getParams()->qbe->{$field};
            if ($v) {
                $context->getBuilder()->field($field)->equals($v);
            }
        };
    }
}
