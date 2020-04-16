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

    /**
     * @param int|string $id
     */
    public function translate(string $entityClass, $id): string
    {
        $high = $this->entityRegistry->getHigh($entityClass);
        foreach ($this->translators as $translator) {
            if ($translator->supports($high, $entityClass, $id)) {
                return $translator->translate($high, $entityClass, $id);
            }
        }

        return strval($id);
    }
}
