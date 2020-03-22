<?php
declare(strict_types=1);

namespace Bungle\Framework\Twig;

use DateTimeInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class BungleTwigExtension extends AbstractExtension {
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('bungle_format', self::class.'::format'),
        ];
    }

    /**
     * Convert value to string, support a few common types:
     *
     * 1. null convert to ''
     * 1. bool convert to '是/否'
     * 1. float convert to thousands separated, and round to 2
     * 1. DateTime format to 'yy-MM-dd hh:mm', if time part is zero, format to 'yy-MM-dd'
     * 1. Other values use `strval()`.
     *
     * @param $v mixed value to format
     */
    public static function format($v): string {
        if ($v === true) {
            return '是';
        } elseif ($v === false) {
            return '否';
        }

        if (is_float($v)){
            return number_format($v, 2, '.', ',');
        }

        if ($v instanceof DateTimeInterface) {
            $r = $v->format('y-m-d H:i');
            return preg_replace('/ 00:00$/', '', $r, 1);
        }
        return strval($v);
    }
}
