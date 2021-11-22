<?php

declare(strict_types=1);

namespace Bungle\Framework\Export;

class ExportProgress
{
    /** -1 if unknown*/
    public int $total = 0;
    public int $current = 0;
    public string $status = '';
    /** Message appended in message view */
    public string $message = '';
    /** Error appended in message view */
    public string $error = '';
    /** Warn appended in message view */
    public string $warn = '';
}
