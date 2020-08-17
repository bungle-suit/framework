<?php
declare(strict_types=1);

namespace Bungle\Framework\Export;

use DateTime;

class DateRange
{
    public ?DateTime $start;
    public ?DateTime $end;

    public function __construct(?DateTime $start, ?DateTime $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * If date range out of three months.
     */
    public function outOfRange(DateTime $today = null): bool
    {
        if ($this->start === null) {
            return true;
        }

        if ($today === null) {
            $today = new DateTime();
            $today->setTime(0, 0);
        }

        [$start, $end] = [$this->start, $this->end ?? $today];
        return $end->diff($start)->days > 92;
    }
}
