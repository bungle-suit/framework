<?php
declare(strict_types=1);

namespace Bungle\Framework\Ent\Code;

use DateTimeInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use MongoDB\Operation\FindOneAndUpdate;
use RangeException;

/**
 * Service generate entity code, or helps to generate entity code.
 */
class CodeGenerator
{
    public const ID_COLLECTION = 'bungle.code_gen';

    /**
     * @var DocumentManager
     */
    private DocumentManager $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Return auto-inced prefixed code, such as:
     *
     *     nextPrefixedCode('foo', 3);
     *
     * will return 'foo001' for the first time, then 'foo002' next time.
     *
     * Raise an Exception if current code is 'foo999'.
     *
     */
    public function nextPrefixedCode(string $prefix, int $nchar): string
    {
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

        $r = $prefix.sprintf("%0${nchar}d", $result['current_id']);
        if (strlen($r) > strlen($prefix) + $nchar) {
            throw new RangeException("Max code reached: $r, n: $nchar");
        }
        return $r;
    }

    /**
     * Returns compacted year month code:
     *
     * Year preserve two digits, such as '20' for '2020', month is one char:
     * 123456789XYZ, X for 10, Y for 11, Z for 12.
     */
    public static function compactYearMonth(DateTimeInterface $d): string
    {
        $y = $d->format('y');
        $m = $d->format('n');
        switch ($m) {
            case '10':
                $m = 'X';
                break;
            case '11':
                $m = 'Y';
                break;
            case '12':
                $m = 'Z';
                break;
        }
        return $y.$m;
    }
}
