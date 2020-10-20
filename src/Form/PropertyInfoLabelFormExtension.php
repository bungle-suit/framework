<?php

declare(strict_types=1);

namespace Bungle\Framework\Form;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * If form type label not set, use PropertyInfo as label.
 */
class PropertyInfoLabelFormExtension extends AbstractTypeExtension
{
    private PropertyInfoExtractorInterface $propertyInfoExtractor;
    private CacheInterface $cache;

    public function __construct(
        PropertyInfoExtractorInterface $propertyInfoExtractor,
        CacheInterface $cache
    ) {
        $this->propertyInfoExtractor = $propertyInfoExtractor;
        $this->cache = $cache;
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($view->vars['label'] !== null) {
            return;
        }

        if (($parent = $form->getParent()) === null) {
            return;
        }

        $data = $parent->getData();
        if (!is_object($data)) {
            return;
        }

        [$name, $cls] = [$form->getName(), get_class($data)];
        $label = $this->cache->get(
            'bungle_auto_label-'.str_replace('\\', '-', $cls).'-'.$name,
            fn () => $this->propertyInfoExtractor->getShortDescription($cls, $name)
        );

        $view->vars['label'] = $label;
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
