<?php

declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\TableReader;

use RuntimeException;

/**
 * @see TableReader::onSectionEnd()
 */
class TableReadException extends RuntimeException
{
    /** @var TableReadRowError[] */
    private array $rowErrors;

    public function __construct(array $rowErrors)
    {
        parent::__construct(self::genMessage($rowErrors), 0, null);
    }

    public function getRowErrors(): array
    {
        return $this->rowErrors;
    }

    /**
     * @param TableReadRowError[] $rowErrors
     */
    private static function genMessage(array $rowErrors): string
    {
        return "导入Excel出现错误:\n\n". implode("\n", $rowErrors);
    }
}
