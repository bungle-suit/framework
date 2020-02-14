<?php
declare(strict_types=1);

namespace Bungle\Framework\Entity;

use Bungle\Framework\Annotation\HighPrefix;

class AnnotationHighResolver implements HighResolverInterface
{
    public function resolveHigh(string $entityCls): ?string
    {
        return HighPrefix::loadHighPrefix($entityCls);
    }
}
