<?php

declare(strict_types=1);

namespace Bungle\Framework\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Apply logic name to class and/or property.
 *
 * @Annotation
 * @Target({"CLASS","PROPERTY","METHOD"})
 */
final class LogicName
{
    /**
     * @Required
     */
    public string $value;

    /**
     * resolve logic name for the specific class.
     *
     * Returns class's short name if LogicName annotation not defined.
     * @param class-string<mixed> $clsName
     */
    public static function resolveClassName(string $clsName): string
    {
        /*
         * Doctrine annotations lib will failed if some annotations class not loaded,
         */
        require_once __DIR__.'/High.php';

        $cls = new ReflectionClass($clsName);

        $reader = new AnnotationReader();
        /** @var ?LogicName $annotation */
        $annotation = $reader->getClassAnnotation($cls, LogicName::class);

        return $annotation ? $annotation->value : self::getShortClassName($clsName);
    }

    /**
     * Resolve property logic names for the specific class.
     *
     * Returns name -> logicName array.
     *
     * Property names not marked with LogicName annotations has entry of
     * name -> name, for easier detect not-defined property.
     *
     * Include inherited properties.
     *
     * Ignores private and protected properties.
     * @param class-string<mixed> $clsName
     * @return array<string, string>
     */
    public static function resolvePropertyNames(string $clsName): array
    {
        /*
         * Doctrine annotations lib will failed if some annotations class not loaded,
         */
        require_once __DIR__.'/High.php';

        $cls = new ReflectionClass($clsName);
        $reader = new AnnotationReader();

        $r = [];
        $flag = ReflectionProperty::IS_PUBLIC +
          ReflectionProperty::IS_PRIVATE +
          ReflectionProperty::IS_PROTECTED;
        foreach ($cls->getProperties($flag) as $p) {
            if ($p->isStatic()) {
                continue;
            }
            /** @var ?LogicName $annotation */
            $annotation = $reader->getPropertyAnnotation($p, LogicName::class);
            $r[$p->getName()] = $annotation ? $annotation->value : $p->getName();
        }
        foreach ($cls->getMethods(ReflectionMethod::IS_PUBLIC) as $m) {
            if (self::isGetter($m)) {
                /** @var ?LogicName $annotation */
                $annotation = $reader->getMethodAnnotation($m, LogicName::class);
                $propName = self::getPropertyNameFromGetter($m->getName());
                if ($annotation === null) {
                    if (!key_exists($propName, $r)) {
                        $r[$propName] = $propName;
                    }
                } else {
                    $r[$propName] = $annotation->value;
                }
            }
        }

        return $r;
    }

    private static function isGetter(ReflectionMethod $method): bool
    {
        $name = $method->getName();
        return strlen($name) > 3 && strpos($name, 'get') === 0;
    }

    private static function getPropertyNameFromGetter(string $name): string
    {
        return lcfirst(substr($name, 3));
    }

    /**
     * Internal use.
     */
    public static function getShortClassName(string $clsName): string
    {
        $r = strrchr($clsName, '\\');

        return $r ? substr($r, 1) : $clsName;
    }
}
