<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute;

use Bungle\Framework\Model\ExtAttribute\AttributeInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Describe attribute
 */
interface AttributeDefinitionInterface
{
    public function getLabel(): string;

    public function getName(): string;

    public function buildForm(FormBuilderInterface $formBuilder, array $options): void;

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
