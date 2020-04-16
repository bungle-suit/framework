<?php
declare(strict_types=1);

namespace Bungle\Framework\IDName;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Unlike AbstractIDNameTranslator, HighIDNameTranslator class accept two arguments: high, and id.
 *
 * Can be used directly without to create sub classes.
 *
 * The actual id-name translation delegate to HighIDNameTranslatorInterfaces, the first resolver
 * supports method returns true, will be used. Translators sort by priority, higher value
 * has higher priority, default is zero, use symfony priority tag. Like:
 *
 * ```
 *   services:
 *     App\Ent\MyIDNameTranslator:
 *     tags:
 *       - { name: bungle.idName, priority: 1 }
 * ```
 *
 * HighIDNameTranslator easier to use than AbstractIDNameTranslator, but with one drawback:
 * a high entity can only has one implementation, to use specific name format, derives a new
 * sub class from AbstractIDNameTranslator.
 *
 * BungleBundle predefined two HighIDNameTranslator, first use name field, 2nd use code field,
 * requires Entity class implement Nameable, or Codeable interface.
 *
 * BungleBundle defined a DI compile process to auto register translator service if tagged with
 * `bungle.idName`
 */
class HighIDNameTranslator
{
    private HighIDNameTranslatorChain $translatorChain;
    private CacheInterface $cache;

    public function __construct(HighIDNameTranslatorChain $translatorChain, CacheInterface $cache)
    {
        $this->translatorChain = $translatorChain;
        $this->cache = $cache;
    }

    /**
     * @param int|string|null $id
     */
    public function idToName(string $high, $id): string
    {
        if ($id === null) {
            return '';
        }

        return $this->cache->get(
            $this->getCacheKey($high, $id),
            fn (ItemInterface $item) => $this->translatorChain->translate($high, $id)
        );
    }

    /**
     * @param int|string|null $id
     */
    public function getCacheKey(string $high, $id): string
    {
        return "highIDName-$high-$id";
    }
}
