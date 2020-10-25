<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class BoolAttributeType extends AbstractType
{
    public function getParent()
    {
        return CheckboxType::class;
    }
}
