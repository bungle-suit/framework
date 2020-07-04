<?php

declare(strict_types=1);

namespace Bungle\Framework\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use UnexpectedValueException;

/**
 * Define high of a entity class.
 *
 * @Annotation
 * @Target("CLASS")
 */
final class High
{
    /**
     * @Required
     */
    public string $value;

    /**
     * Resolve high prefix for the specific class.
     *
     * Returns null if the Annotation not defined.
     */
    public static function resolveHigh(string $clsName): ?string
    {
        $cls = new ReflectionClass($clsName);
        $reader = new AnnotationReader();
        $anno = $reader->getClassAnnotation($cls, High::class);
        if (!$anno) {
            return null;
        }

        $high = $anno->value;
        if (0 === preg_match('/^[a-z]{3}$/', $high)) {
            throw new UnexpectedValueException("Invalid format of high value '$high' of Entity '$clsName'");
        }

        return $high;
    }
}
