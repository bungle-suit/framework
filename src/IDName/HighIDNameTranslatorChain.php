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
    public function translate(string $high, $id): string
    {
        $entityClass = $this->entityRegistry->getEntityByHigh($high);
        foreach ($this->translators as $translator) {
            if ($translator->supports($high, $entityClass, $id)) {
                return $translator->translate($high, $entityClass, $id);
            }
        }

        return strval($id);
    }
}
