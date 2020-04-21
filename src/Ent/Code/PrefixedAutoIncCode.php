<?php
declare(strict_types=1);

namespace Bungle\Framework\Ent\Code;

use Doctrine\ODM\MongoDB\DocumentManager;
use LogicException;
use MongoDB\Operation\FindOneAndUpdate;
use RangeException;

/**
 * Code Generate step use current $result as prefix, append
 * with auto inc code, such as:
 *
 *      (new PrefixedAutoIncCode($dm, 3))(3);
 *
 * will return 'foo001' for the first time, then 'foo002' next time.
 *
 * Raise an Exception if current code is 'foo999'.
 */
class PrefixedAutoIncCode
{
    public const ID_COLLECTION = 'bungle.code_gen';
    private DocumentManager $dm;
    private int $n;

    public function __construct(DocumentManager $dm, int $n)
    {
        $this->dm = $dm;
        $this->n = $n;
    }

    /**
     * Code generate step callback.
     */
    public function __invoke(object $subject, CodeContext $ctx): void
    {
        $prefix = $ctx->result;
        if (!$prefix) {
            throw new LogicException('PrefixedCodeAutoIncCode prefix/$result should not be empty');
        }

        $db = $this->dm->getConfiguration()->getDefaultDB();
        $coll = $this->dm->getClient()->selectCollection($db, self::ID_COLLECTION);
        $query   = ['_id' => $prefix, 'current_id' => ['$exists' => true]];
        $update  = ['$inc' => ['current_id' => 1]];
        $options = ['upsert' => false, 'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER];
        $result  = $coll->findOneAndUpdate($query, $update, $options);

        /*
         * Updated nothing - counter doesn't exist, creating new counter.
         * Not bothering with {$exists: false} in the criteria as that won't avoid
         * an exception during a possible race condition.
         */
        if ($result === null) {
            $query   = ['_id' => $prefix];
            $update  = ['$inc' => ['current_id' => 1]];
            $options = ['upsert' => true, 'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER];
            $coll->findOneAndUpdate($query, $update, $options);

            $result = ['current_id' => 1];
        }

        $r = $prefix.sprintf("%0{$this->n}d", $result['current_id']);
        if (strlen($r) > strlen($prefix) + $this->n) {
            throw new RangeException("Max code reached: $r, n: $this->n");
        }
        $ctx->result = $r;
    }
}
