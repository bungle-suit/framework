<?php

declare(strict_types=1);

namespace Bungle\Framework\Export\ExcelWriter\TablePlugins;

use Bungle\Framework\Export\ExcelWriter\TableContext;
use Bungle\Framework\Export\ExcelWriter\TablePluginInterface;

class CompositeTablePlugin implements TablePluginInterface
{
    private array $plugins;

    /**
     * @param TablePluginInterface[] $plugins
     */
    public function __construct(array $plugins)
    {
        $this->plugins = $plugins;
    }

    public function onTableStart(TableContext $context): void
    {
        foreach ($this->plugins as $plugin) {
            $plugin->onTableStart($context);
        }
    }

    public function onRowFinish(array $rows, TableContext $context): void
    {
        foreach ($this->plugins as $plugin) {
            $plugin->onRowFinish($rows, $context);
        }
    }

    public function onTableFinish(TableContext $context): void
    {
        foreach ($this->plugins as $plugin) {
            $plugin->onTableFinish($context);
        }
    }

    public function onHeaderFinish(TableContext $context): void
    {
        foreach ($this->plugins as $plugin) {
            $plugin->onHeaderFinish($context);
        }
    }
}
