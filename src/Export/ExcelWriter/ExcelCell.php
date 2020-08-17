<?php
declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter;

use DateTime;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * Excel cell with option format, style, margins.
 */
class ExcelCell
{
    /**
     * formatCode option name
     */
    public const OPTION_FORMAT_CODE = 'formatCode';

    /** @var mixed */
    public $value;

    /**
     * @phpstan-var array{
     *      formatCode?: string,
     * }
     */
    public array $options;

    /**
     * Cell width span and height span.
     *
     * @phpstan-var array{int, int}|null
     */
    public ?array $span;

    /**
     * @param mixed $value
     * @phpstan-param array{int, int}|null $span
     * @phpstan-param array{
     *      formatCode?: string,
     * } $options
     *
     * formatCode: one of PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_XXX const
     */
    public function __construct($value, ?array $span = null, array $options = [])
    {
        $this->value = $value;
        $this->options = $options;
        $this->span = $span;
    }

    /**
     * @phpstan-param array{int, int}|null $span
     */
    public static function newDateCell(?DateTime $date, ?array $span = null): self
    {
        if ($date === null) {
            return new self('', $span);
        }

        return new self(
            Date::PHPToExcel($date),
            $span,
            [self::OPTION_FORMAT_CODE => NumberFormat::FORMAT_DATE_YYYYMMDD]
        );
    }
}
