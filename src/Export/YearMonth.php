<?php

namespace Bungle\Framework\Export;

class YearMonth
{
    public function __construct(private int $year, private int $month = 0)
    {
        $v = intdiv($month - 1, 12);
        if ($v) {
            $this->year += $v;
            $this->month = $month % 12;
        }
    }

    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * Return month part, 0 means the full year
     */
    public function getMonth(): int
    {
        return $this->month;
    }

    /**
     * @return string in yyyy-mm-dd format
     */
    public function getFirstDay(): string
    {
        $m = $this->month ?: 1;

        return sprintf('%d-%02d-01', $this->year, $m);
    }

    /**
     * @return string in yyyy-mm-dd, 2021-07-01, if current is 2021-06.
     */
    public function getNextDayOfLastDay(): string
    {
        $next = $this->month ? new self($this->year, $this->month + 1) : new self($this->year + 1);

        return $next->getFirstDay();
    }

    /**
     * $arr at least has one item, the year, 2nd is month, which can be null
     */
    public static function fromArray(array $arr): self
    {
        return new self($arr[0], $arr[1] ?? 0);
    }

    public function __toString(): string
    {
        if ($this->month === 0) {
            return strval($this->year);
        }

        return sprintf('%d-%02d', $this->year, $this->month);
    }
}
