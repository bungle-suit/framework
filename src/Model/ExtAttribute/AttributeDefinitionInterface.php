<?php
declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Describe attribute
 */
interface AttributeDefinitionInterface
{
    public function getLabel(): string;

    public function getName(): string;

    public function getDescription(): string;

    public function buildForm(FormBuilderInterface $formBuilder, array $options): void;

    /**
     * Return symfony form type
     */
    public function getFormType(): string;

    /**
     * Return symfony form options, no need set 'required', 'label', 'description' options,
     * these are set by caller.
     */
    public function getFormOption(): array;

    /**
     * @return mixed create attribute default value
     */
    public function createDefault();

    /**
     * @return mixed restore value from $attribute.
     */
    public function restoreValue(AttributeInterface $attribute);

    /**
     * Save current value to $attribute.
     *
     * If current value is default, saved value should be empty string, which won't
     * save to external storage, such as DB or file.
     *
     * @param mixed $value
     */
    public function saveValue(AttributeInterface $attribute, $value): void;
}
