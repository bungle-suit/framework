<?php

declare(strict_types=1);

namespace Bungle\Framework\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * Apply logic name to class and/or property.
 *
 * @Annotation
 * @Target({"CLASS","PROPERTY"})
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
     */
    public static function resolveClassName(string $clsName): string
    {
        /*
         * Doctrine annotations lib will failed if some annotations class not loaded,
         */
        require_once __DIR__.'/HighPrefix.php';

        $cls = new \ReflectionClass($clsName);

        $reader = new AnnotationReader();
        $anno = $reader->getClassAnnotation($cls, LogicName::class);
        return $anno ? $anno->value : self::getShortClassName($clsName);
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
     */
    public static function resolvePropertyNames(string $clsName): array
    {
        /*
         * Doctrine annotations lib will failed if some annotations class not loaded,
         */
        require_once __DIR__.'/HighPrefix.php';

        $cls = new \ReflectionClass($clsName);
        $reader = new AnnotationReader();

        $r = [];
        foreach ($cls->getProperties(\ReflectionProperty::IS_PUBLIC) as $p) {
            if ($p->isStatic()) {
                continue;
            }
            $anno = $reader->getPropertyAnnotation($p, LogicName::class);
            $r[$p->getName()] = $anno ? $anno->value : $p->getName();
        }
        return $r;
    }

    /**
     * Internal use
     */
    public static function getShortClassName(string $clsName): string
    {
        $r = strrchr($clsName, '\\');
        return $r ? substr($r, 1) : $clsName;
    }
}
