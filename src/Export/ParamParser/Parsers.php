<?php
declare(strict_types=1);

namespace Bungle\Framework\Export\ParamParser;

use Bungle\Framework\Ent\BasalInfoService;
use Bungle\Framework\Export\DateRange;
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
     * @return callable(FlowContext): string
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
     * @param bool $mustInThreeMonth failed if parsed data range out of three months.
     * @return callable(FlowContext): mixed[]|string
     */
    public function parseDateRange(
        string $paramName,
        string $startName,
        string $endName,
        bool $mustInThreeMonth = true
    ): callable {
        return function (ExportContext $context) use ($paramName, $mustInThreeMonth, $endName, $startName): ?string {
            $start = self::parseDate($context->getRequest()->get($startName));
            $end = self::parseDate($context->getRequest()->get($endName));
            $range = new DateRange($start, $end);
            if ($mustInThreeMonth && $range->outOfRange($this->basal->today())) {
                return '只能导出三个月内的数据';
            }
            $context->set($paramName, $range);
            return null;
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
     * @return callable(FlowContext): string
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
