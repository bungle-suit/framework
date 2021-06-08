<?php

namespace Bungle\Framework\Tests\Export;

use Bungle\Framework\Export\DateRange;
use Bungle\Framework\Export\YearMonth;
use DateTime;

it('year month and toString', function ($exp, $year, $month, $expYear = null, $expMonth = null) {
    $expYear = $expYear ?? $year;
    $expMonth = $expMonth ?? $month;
    $v = new YearMonth($year, $month);
    expect($v->getYear())->toBe($expYear);
    expect($v->getMonth())->toBe($expMonth);
    expect($v->__toString())->toEqual($exp);
})->with(
    [
        'year only' => ['2021', 2021, 0],
        'padding month' => ['2021-05', 2021, 5],
        'year month' => ['2022-12', 2022, 12],
        'month > 12' => ['2022-01', 2021, 13, 2022, 1],
        'month > 24' => ['2023-01', 2021, 25, 2023, 1],
    ]
);

it('first last day', function ($first, $nextDayOfLastDay, $y, $m = 0) {
    $v = new YearMonth($y, $m);
    expect($v->getFirstDay())->toEqual($first);
    expect($v->getNextDayOfLastDay())->toEqual($nextDayOfLastDay);
})->with(
    [
        'year only' => ['2021-01-01', '2022-01-01', 2021],
        'year month' => ['2021-05-01', '2021-06-01', 2021, 5],
        'cross year' => ['2021-12-01', '2022-01-01', 2021, 12],
    ]
);

it('from array', function ($exp, $arr) {
    expect(strval(YearMonth::fromArray($arr)))->toBe($exp);
})->with(
    [
        'year only' => ['2021', [2021]],
        'year only null month' => ['2021', [2021, null]],
        'year only zero month' => ['2021', [2021, 0]],
        'year month' => ['2021-06', [2021, 6]],
        'year month > 12' => ['2022-02', [2021, 14]],
    ]
);

it('to date range', function () {
    $v = new YearMonth(2021, 1);
    expect($v->toDateRange())
        ->toEqual(new DateRange(
                      new DateTime('2021-01-01'),
                      new DateTime('2021-01-31'),
                  ));
});
