<?php
declare(strict_types=1);

namespace Bungle\Framework\IDName;

use DateInterval;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

abstract class AbstractIDNameTranslator
{
    private CacheInterface $cache;

    /**
     * AbstractIDNameTranslator constructor.
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Convert id to its name.
     *
     * @param int|string $id
     */
    public function idToName($id): string
    {
        return $this->cache->get('IdName-'.$id, function (ItemInterface $item) use($id) {
            $item->expiresAfter(new DateInterval('PT10M'));
            return $this->doIdToName($id);
        });
    }

    /**
     * @param int|string $id
     */
    abstract protected function doIdToName($id): string;
}
