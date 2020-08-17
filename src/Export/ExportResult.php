<?php
declare(strict_types=1);

namespace Bungle\Framework\Export;

class ExportResult
{
    /**
     * Path where the generated file content
     */
    public string $tempFile;

    /**
     * Logic filename
     */
    public string $logicFilename;

    public function __construct(string $tempFile, string $logicFilename)
    {
        $this->tempFile = $tempFile;
        $this->logicFilename = $logicFilename;
    }
}
