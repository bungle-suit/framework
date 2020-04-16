<?php
declare(strict_types=1);

namespace Bungle\Framework\Ent\IDName;

use DateInterval;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

abstract class AbstractIDNameTranslator
{
    private CacheInterface $cache;
    private string $high;

    /**
     * AbstractIDNameTranslator constructor.
     * @param string $high Part of cache key, such as entity high prefix.
     */
    public function __construct(string $high, CacheInterface $cache)
    {
        $this->cache = $cache;
        $this->high = $high;
    }

    /**
     * Convert id to its name.
     *
     * @param int|string $id
     */
    public function idToName($id): string
    {
        if ($id === null) {
            return '';
        }

        return $this->cache->get($this->getCacheKey($id), function (ItemInterface $item) use($id) {
            $item->expiresAfter(new DateInterval('PT10M'));
            return $this->doIdToName($id);
        });
    }

    /**
     * @param int|string $id
     */
    public function getCacheKey($id): string
    {
        return "IdName-{$this->high}-$id";
    }

    /**
     * @param int|string $id
     */
    abstract protected function doIdToName($id): string;
}
