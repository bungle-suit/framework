<?php

declare(strict_types=1);

namespace Bungle\Framework\Ent\Code;

use Bungle\Framework\Ent\BasalInfoService;

/**
 * Define common code steps.
 */
class CodeSteps
{
    /**
     * Callable string of compactYearMonth step.
     */
    public const COMPACT_YEAR_MONTH = self::class.'::compactYearMonth';

    private BasalInfoService $basal;

    public function __construct(BasalInfoService $basal)
    {
        $this->basal = $basal;
    }

    /**
     * Returns step callable appends literal string section.
     * @noinspection PhpUnusedParameterInspection
     */
    public static function literal(string $s): callable
    {
        return function (object $subject, CodeContext $ctx) use ($s) {
            $ctx->addSection($s);
        };
    }

    /**
     * Returns step callable join sections into result.
     */
    public static function join(string $sep): callable
    {
        return fn(object $subject, CodeContext $ctx) => $ctx->result = implode(
            $sep,
            $ctx->getSections()
        );
    }

    /**
     * Return a code step that format current datetime using $format.
     *
     * @return callable(): string
     */
    public function dateTime(string $format): callable
    {
        return fn(): string => $this->basal->now()->format($format);
    }
}
