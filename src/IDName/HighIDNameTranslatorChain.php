<?php
declare(strict_types=1);

namespace Bungle\Framework\IDName;

use Bungle\Framework\Entity\EntityRegistry;

/**
 * @internal helper class for HighIDNameTranslator
 */
class HighIDNameTranslatorChain
{
    private EntityRegistry $entityRegistry;
    private array $translators;

    /**
     * @param HighIDNameTranslatorInterface[] $translators
     */
    public function __construct(EntityRegistry $entityRegistry, array $translators)
    {
        $this->entityRegistry = $entityRegistry;
        $this->translators = $translators;
    }

    public function translate(string $high, $id): string
    {
        //
    }
}
