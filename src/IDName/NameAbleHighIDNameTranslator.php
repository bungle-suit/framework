<?php
declare(strict_types=1);

namespace Bungle\Framework\IDName;

use Bungle\Framework\Entity\CommonTraits\NameAbleInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * If entity class implement NameAbleInterface, use name field as name.
 *
 * BungleBundle registered this translator by lower priority, create your
 * Translator with normal priority override it.
 */
class NameAbleHighIDNameTranslator implements HighIDNameTranslatorInterface
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function supports(string $high, string $entityClass, $id): bool
    {
        $interfaces = class_implements($entityClass);
        return $interfaces !== false && in_array(NameAbleInterface::class, $interfaces);
    }

    public function translate(string $high, string $entityClass, $id): string
    {
        $qb = $this->dm
            ->createQueryBuilder($entityClass)
            ->select(['name'])
            ->readOnly(true)
            ->hydrate(false)
            ->field('_id')
            ->equals($id);
        $rs = $qb->getQuery()->getIterator();

        foreach ($rs as $rec) {
            return $rec['name'];
        }
        return strval($id);
    }
}
