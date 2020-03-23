<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

use Bungle\Framework\Annotation\High;

class AnnotationHighResolver implements HighResolverInterface
{
    public function resolveHigh(string $entityCls): ?string
    {
        return High::resolveHigh($entityCls);
    }
}
