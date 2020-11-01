<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter\TablePlugins;

use Bungle\Framework\Export\ExcelWriter\TableContext;
use Bungle\Framework\Export\ExcelWriter\TablePluginInterface;

/**
 * Abstract implementation of @see TablePluginInterface
 */
abstract class AbstractTablePlugin implements TablePluginInterface
{
    public function onTableStart(TableContext $context): void
    {
    }

    public function onRowFinish(array $rows, TableContext $context): void
    {
    }

    public function onTableFinish(TableContext $context): void
    {
    }
}
