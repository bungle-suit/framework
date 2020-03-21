<?php
declare(strict_types=1);

namespace Bungle\Framework\Inquiry;

use Doctrine\ODM\MongoDB\DocumentManager;

class DBProvider implements DBProviderInterface
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $docManager)
    {
        $this->dm = $docManager;
    }

    public function count(Query $q): int
    {
        $qb = $this
            ->dm
            ->createQueryBuilder($q->docClass)
            ->readOnly()
        ;
        foreach ($q->conditions as $field => $cond) {
            $qb->field($field);
            $cond->build($qb);
        }

        return $qb->getQuery()->execute();
    }

    public function search(Query $q): iterable
    {
        $qb = $this
            ->dm
            ->createQueryBuilder($q->docClass)
            ->readOnly()
        ;
        if ($q->fields) {
          $qb->select($q->fields);
        }
        foreach ($q->conditions as $field => $cond) {
            $qb->field($field);
            $cond->build($qb);
        }
        if ($q->offset) {
            $qb->skip($q->offset);
        }
        if (-1 != $q->count) {
            $qb->limit($q->count);
        }

        return $qb->getQuery()->getIterator();
    }
}
