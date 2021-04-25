<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Export\ExcelWriter;

use Bungle\Framework\Export\ExcelWriter\ExcelColumn;
use DateTime;

it('ExcelColumn constructor', function() {
    $col = new ExcelColumn('foo', '[0]');
    expect($col->getHeader())->toBe('foo');
    expect($col->getValueConverter()('foo', 1, ['row']))->toBe('foo');
    expect($col->getPropertyPath())->toBe('[0]');
});

it('date excel column', function() {
    $col = ExcelColumn::createDate('foo', 'a.b');
    $f = $col->getValueConverter();
    expect($f(new DateTime(), 1, ['row']))->toBeFloat();
    expect($f(null, 2, ['row']))->toBeNull();
});

it('formula excel column', function() {
    $col = new ExcelColumn('foo', '');
    expect($col->formulaEnabled())->toBeBool();

    $gen = fn(int $row) => '=expr';
    expect($col->setFormula($gen))->toBe($col);
    expect($col->formulaEnabled())->toBeTrue();
    expect($col->getFormula())->toBe($gen);
});
