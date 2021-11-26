<?php

declare(strict_types=1);

namespace Bungle\Framework\Export;

use Bungle\Framework\Converter;
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
     * @param int $maxDays max days of allowed date range, 0 to disable the check.
     */
    public function outOfRange(int $maxDays, DateTime $today = null): bool
    {
        if (!$maxDays) {
            return false;
        }

        if ($this->start === null) {
            return true;
        }

        if ($today === null) {
            $today = new DateTime();
            $today->setTime(0, 0);
        }

        [$start, $end] = [$this->start, $this->end ?? $today];

        return $end->diff($start)->days > $maxDays;
    }

    public function __toString(): string
    {
        if ($this->start === null && $this->end === null) {
            return '';
        }

        return Converter::formatYMD($this->start).' ~ '.Converter::formatYMD($this->end);
    }
}
