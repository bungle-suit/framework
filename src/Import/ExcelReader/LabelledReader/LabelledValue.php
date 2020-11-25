<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\LabelledReader;

/**
 * @phpstan-template T of object
 * @implements AbstractLabelledValue<T>
 */
class LabelledValue extends AbstractLabelledValue
{
    private string $label;

    public function __construct(string $path, string $label)
    {
        parent::__construct($path);

        $this->label = $label;
    }

    /**
     * @phpstan-param Context<T> $context
     */
    public function labelMatches(string $label, Context $context): bool
    {
        return $this->label === $label;
    }
}
