<?php
declare(strict_types=1);

namespace Bungle\Framework;

use DateTimeInterface;
use Symfony\Component\String\UnicodeString;

class Converter
{
    /**
     * Convert value to string, support a few common types:
     *
     * 1. null value convert to ''
     * 1. bool convert to '是/否'
     * 1. float convert to thousands separated, and round to 2
     * 1. DateTime format to 'yy-MM-dd hh:mm', if time part is zero, format to 'yy-MM-dd'
     * 1. Other values use `strval()`.
     *
     * @param $v mixed value to format
     */
    public static function format($v): string
    {
        if (true === $v) {
            return '是';
        } elseif ($v === false) {
            return '否';
        }

        if (is_float($v)) {
            return number_format($v, 2, '.', ',');
        }

        if ($v instanceof DateTimeInterface) {
            $r = $v->format('y-m-d H:i');
            return preg_replace('/ 00:00$/', '', $r, 1);
        }
        return strval($v);
    }

    private const HALF_SPACE = ' ';
    private const FULL_SPACE = '　';

    /**
     * Add spaces to string if length of $s less than minimal length
     */
    public static function justifyAlign(string $s, int $width): string
    {
        $s = new UnicodeString($s);
        $len = $s->length();
        $spare = $width - $len;

        if ($spare <= 0 || $len === 0) {
            return $s->toString();
        }

        if ($len == 1 && $width > $len) {
            return str_repeat(self::FULL_SPACE, $spare).$s;
        }

        $spare *= 2;
        $slot = $len - 1;
        $blanksPerSlot = $spare / $slot;
        $sb = $s->slice(0, 1);
        $blanks = 0;

        for ($i = 0; $i < $slot; $i++) {
            $blanks += $blanksPerSlot;

            if ($blanks >= 1) {
                $b = (int)$blanks;
                $sb = $sb->append(str_repeat(self::HALF_SPACE, $b));
                $blanks -= $b;
            }

            if ($blanks > 0 && $i === $slot - 1) {
                $sb = $sb->append(self::HALF_SPACE);
            }

            $sb = $sb->append($s->slice($i + 1, 1)->toString());
        }

        return $sb->replace('  ', self::FULL_SPACE)->toString();
    }
}
