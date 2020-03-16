<?php

declare(strict_types=1);

namespace Bungle\Framework\Entity;

use Bungle\Framework\Annotation\LogicName;
use Doctrine\ODM\MongoDB\DocumentManager;

final class ODMEntityMetaResolver implements EntityMetaResolverInterface
{
    private DocumentManager $docManager;

    public function __construct(DocumentManager $docManager)
    {
        $this->docManager = $docManager;
    }

    public function resolveEntityMeta(string $entityClass): EntityMeta
    {
        $classMeta = $this->docManager->getClassMetadata($entityClass);
        $clsLogicName = LogicName::resolveClassName($entityClass);
        $propLogicNames = LogicName::resolvePropertyNames($entityClass);

        $propMetas = [];
        foreach ($classMeta->getFieldNames() as $fieldName) {
            $propMetas[] = new EntityPropertyMeta(
                $fieldName,
                $propLogicNames[$fieldName],
                $classMeta->getTypeOfField($fieldName),
            );
        }

        return new EntityMeta($entityClass, $clsLogicName, $propMetas);
    }
}
