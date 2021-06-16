<?php

namespace Bungle\Framework\Helper;

class ClassUtil
{
    public static function getShortClassName(string $s): string
    {
        $idx = strrchr($s, '\\');
        if (false === $idx) {
            return $s;
        }

        return substr($idx, 1);
    }
}
