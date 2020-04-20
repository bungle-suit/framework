<?php
declare(strict_types=1);

namespace Bungle\Framework\Ent\Code;

use DateTime;
use DateTimeInterface;

/**
 * Define common code steps.
 */
class CodeSteps
{
    /**
     * Callable string of compactYearMonth step.
     */
    public const COMPACT_YEAR_MONTH = self::class.'::compactYearMonth';

    /**
     * Year preserve two digits, such as '20' for '2020', month is one char:
     * 123456789XYZ, X for 10, Y for 11, Z for 12.
     */
    public static function compactYearMonth(object $subject, CodeContext $ctx, DateTimeInterface $d = null): void
    {
        $d = $d ?? new DateTime();
        $y = $d->format('y');
        $m = $d->format('n');
        switch ($m) {
            case '10':
                $m = 'X';
                break;
            case '11':
                $m = 'Y';
                break;
            case '12':
                $m = 'Z';
                break;
        }
        $ctx->addSection($y.$m);
    }
}
