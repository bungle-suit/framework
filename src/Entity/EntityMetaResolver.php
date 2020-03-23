<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

use Bungle\Framework\Annotation\LogicName;

final class EntityMetaResolver implements EntityMetaResolverInterface
{
    public function resolveEntityMeta(string $entityClass): EntityMeta
    {
        $clsLogicName = LogicName::resolveClassName($entityClass);
        $propLogicNames = LogicName::resolvePropertyNames($entityClass);

        $propMetas = [];
        foreach ($propLogicNames as $fieldName => $logicName) {
            $propMetas[] = new EntityPropertyMeta(
                $fieldName,
                $logicName,
            );
        }

        return new EntityMeta($entityClass, $clsLogicName, $propMetas);
    }
}
