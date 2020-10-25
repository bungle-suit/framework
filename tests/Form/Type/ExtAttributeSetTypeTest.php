<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Form\Type;

use Bungle\Framework\Form\DataMapper\AttributeSetNormalizer;
use Bungle\Framework\Form\Type\BaseExtAttributeSetType;
use Bungle\Framework\Tests\Model\ExtAttribute\TestNormalizedAttributeSet;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Form\FormBuilderInterface;

class ExtAttributeSetTypeTest extends MockeryTestCase
{
    public function testBuildForm(): void
    {
        $builder = Mockery::mock(FormBuilderInterface::class);
        $builder->expects('addModelTransformer')->with(
            Mockery::type(AttributeSetNormalizer::class)
        );
        $builder->expects('add')->with('a');
        $builder->expects('add')->with('b');
        $builder->expects('add')->with('c');

        $type = new BaseExtAttributeSetType(TestNormalizedAttributeSet::class);
        $type->buildForm($builder, []);
    }
}
