<?php

declare(strict_types=1);

namespace Bungle\Framework\Helper;

use DateInterval;
use DateTime;

class DateTimeHelper
{
    /**
     * Add $dateTime with $days, return a new instance, $dateTime not changed.
     */
    public static function addDays(DateTime $dateTime, int $days): DateTime
    {
        $dateTime = clone $dateTime;

        if ($days < 0) {
            $days = abs($days);

            return $dateTime->sub(new DateInterval("P{$days}D"));
        }

        return $dateTime->add(new DateInterval("P{$days}D"));
    }
}
