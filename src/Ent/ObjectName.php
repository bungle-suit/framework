<?php
declare(strict_types=1);

namespace Bungle\Framework\Ent;

use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Get UI friendly name from object/class (normally entity object).
 *
 * Name resolved from phpdoc short description.
 */
class ObjectName
{
    private DocBlockFactory $docBlockFactory;
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->docBlockFactory = DocBlockFactory::createInstance();
        $this->cache = $cache;
    }

    /**
     * @param string|object $clsOrInstance
     * @phpstan-param class-string|object $clsOrInstance
     */
    public function getName($clsOrInstance): string
    {
        if (!is_string($clsOrInstance)) {
            $clsOrInstance = get_class($clsOrInstance);
        }
        $key = 'ObjectName-'.str_replace('\\', '_', $clsOrInstance);

        return $this->cache->get($key, fn () => $this->resolveName($clsOrInstance));
    }

    /**
     * @param class-string<mixed> $clsName
     */
    private function resolveName(string $clsName): string
    {
        $cls = new ReflectionClass($clsName);
        if ($cls->getDocComment() === false) {
            return $cls->getShortName();
        }

        $doc = $this->docBlockFactory->create($cls);

        $r = $doc->getSummary();
        return $r ? $r : $cls->getShortName();
    }
}
