<?php

declare(strict_types=1);

namespace Bungle\Framework\Helper;

class PathUtil
{
    /**
     * Return true if $path extension is $ext, ext compared using ignored case.
     * @param string $ext Extension no leading '.', such as 'txt' instead of '.txt'
     */
    public static function hasExt(string $ext, string $path): bool
    {
        $act = pathinfo($path, PATHINFO_EXTENSION);

        return strcasecmp($ext, $act) === 0;
    }
}
