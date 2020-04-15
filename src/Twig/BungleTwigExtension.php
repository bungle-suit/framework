<?php
declare(strict_types=1);

namespace Bungle\Framework\Twig;

use Bungle\Framework\Converter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class BungleTwigExtension extends AbstractExtension {
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('bungle_format', Converter::class.'::format'),
        ];
    }
}
