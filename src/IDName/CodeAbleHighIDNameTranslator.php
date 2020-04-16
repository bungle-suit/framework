<?php
declare(strict_types=1);

namespace Bungle\Framework\IDName;

use Bungle\Framework\Entity\CommonTraits\CodeAbleInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

class CodeAbleHighIDNameTranslator implements HighIDNameTranslatorInterface
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function supports(string $high, string $entityClass, $id): bool
    {
        $interfaces = class_implements($entityClass);
        return $interfaces !== false && in_array(CodeAbleInterface::class, $interfaces);
    }

    public function translate(string $high, string $entityClass, $id): string
    {
        $qb = $this->dm
            ->createQueryBuilder($entityClass)
            ->select(['code'])
            ->readOnly(true)
            ->hydrate(false)
            ->field('_id')
            ->equals($id);
        $rs = $qb->getQuery()->getIterator();

        foreach ($rs as $rec) {
            return $rec['code'];
        }
        return strval($id);
    }
}
