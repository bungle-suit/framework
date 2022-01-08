<?php
declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

/**
 * Describe attribute
 */
interface AttributeDefinitionInterface
{
    public function getLabel(): string;

    public function getName(): string;

    public function getDescription(): string;

    /**
     * Return symfony form type
     */
    public function getFormType(): string;

    /**
     * Return symfony form options, no need set 'required', 'label', 'description' options,
     * these are set by caller.
     * @return array<string, mixed>
     */
    public function getFormOption(): array;

    /**
     * @return mixed create attribute default value
     */
    public function createDefault(): mixed;

    /**
     * @return mixed restore value from $attribute.
     */
    public function restoreValue(AttributeInterface $attribute);

    /**
     * Save current value to $attribute.
     *
     * If current value is default, saved value should be empty string, which won't
     * save to external storage, such as DB or file.
     */
    public function saveValue(AttributeInterface $attribute, mixed $value): void;
}
