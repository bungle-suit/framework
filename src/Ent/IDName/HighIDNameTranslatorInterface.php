<?php
declare(strict_types=1);

namespace Bungle\Framework\Ent\IDName;

/**
 * @see HighIDNameTranslator
 */
interface HighIDNameTranslatorInterface
{
    /**
     * @param int|string $id
     */
    public function supports(string $high, string $entityClass, $id): bool;

    /**
     * @param int|string $id
     */
    public function translate(string $high, string $entityClass, $id): string;
}
