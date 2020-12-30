<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ParamParser;

use Bungle\Framework\Ent\BasalInfoService;
use Bungle\Framework\Export\DateRange;
use Bungle\Framework\FP;
use DateTime;

class Parsers
{
    /**
     * Parameter name of current user, @see self::currentUser()
     */
    public const PARAM_CURRENT_USER = 'current_user';

    /** @required */
    private BasalInfoService $basal;

    public function __construct(BasalInfoService $basal)
    {
        $this->basal = $basal;
    }

    /**
     * Create value parser that checks $attrName attribute exist in context.
     *
     * @return callable(ExportContext): ?string
     */
    public static function ensureAttrExist(string $attrName): callable
    {
        return function (ExportContext $context) use ($attrName): ?string {
            if (!$context->has($attrName)) {
                return "Required \"$attrName\" attribute";
            }

            return null;
        };
    }

    /**
     * Parse parameter from ExportContext attribute.
     *
     * @param mixed $default
     * @return callable(ExportContext): ?string
     */
    public static function fromContextAttribute(string $attrName, $default = null): callable
    {
        return function (ExportContext $context) use ($attrName, $default) {
            $v = $context->get($attrName, $default);
            $context->set($attrName, $v);

            return null;
        };
    }

    /**
     * Create value converter that get param from request argument (query string or post).
     *
     * If $paramName not exist in request, null value saved into context.
     *
     * @param mixed $default use default if param not exist.
     * @param callable(string): mixed $converter, by default use identity, raise RuntimeException on
     * error.
     */
    public static function fromRequest(
        string $paramName,
        $default = null,
        callable $converter = null
    ): callable {
        return function (ExportContext $ctx) use ($default, $paramName, $converter): ?string {
            $f = $converter ?? [FP::class, 'identity'];
            $request = $ctx->getRequest();
            $v = $request->get($paramName);
            if ($v === null) {
                $v = $default;
            } else {
                $v = $f($v);
            }
            $ctx->set($paramName, $v);

            return null;
        };
    }

    /**
     * Param parser put current user into key self::PARAM_CURRENT_USER.
     */
    public function currentUser(ExportContext $context): ?string
    {
        $context->set(self::PARAM_CURRENT_USER, $this->basal->currentUser());

        return null;
    }

    /**
     * Return a parser parses date range.
     *
     * @param string $paramName name for parsed DateRange param.
     * @param string $startName name for begin of the date range
     * @param string $endName name for the end of the date range
     * @param int $maxDateRange if not 0, failed if the data range out of specific days.
     * @return callable(ExportContext): ?string
     */
    public function parseDateRange(
        string $paramName,
        string $startName,
        string $endName,
        int $maxDateRange = 0
    ): callable {
        return function (ExportContext $context) use (
            $maxDateRange,
            $paramName,
            $endName,
            $startName
        ): ?string {
            $start = self::parseDate($context->getRequest()->get($startName));
            $end = self::parseDate($context->getRequest()->get($endName));
            $range = new DateRange($start, $end);
            if ($range->outOfRange($maxDateRange, $this->basal->today())) {
                return "只能导出{$maxDateRange}天内的数据";
            }
            $context->set($paramName, $range);

            return null;
        };
    }

    /**
     * Returns a parser that failed if all $rangeParamNames are out of $maxDays.
     * @return callable(ExportContext): ?string
     */
    public static function ensureDateRanges(int $maxDays, string ...$rangeParamNames): callable
    {
        return function (ExportContext $context) use ($rangeParamNames, $maxDays): ?string {
            foreach ($rangeParamNames as $name) {
                /** @var ?DateRange $v */
                $v = $context->get($name);
                if ($v && !$v->outOfRange($maxDays)) {
                    return null;
                }
            }

            return "只能导出${maxDays}天内的数据";
        };
    }

    public static function parseDate(?string $s): ?DateTime
    {
        if (null == $s) {
            return null;
        }

        return new DateTime($s);
    }

    /**
     * Create value parser that split query/post param into array
     *
     * @return callable(ExportContext): ?string
     */
    public static function explode(string $attrName, string $sep = ','): callable
    {
        return function (ExportContext $context) use ($sep, $attrName): ?string {
            $s = $context->getRequest()->get($attrName);
            if (!$s) {
                $words = [];
            } else {
                $words = explode($sep, $s);
            }
            $context->set($attrName, $words);

            return null;
        };
    }
}
