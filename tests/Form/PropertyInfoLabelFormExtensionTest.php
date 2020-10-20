<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Form;

use Bungle\Framework\Form\PropertyInfoLabelFormExtension;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

class PropertyInfoLabelFormExtensionTest extends MockeryTestCase
{
    /** @var Mockery\MockInterface|FormInterface */
    private $root;
    /** @var Mockery\MockInterface|PropertyInfoExtractorInterface */
    private $propExtractor;
    private PropertyInfoLabelFormExtension $ext;
    /** @var Mockery\MockInterface|FormInterface */
    private $form;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = Mockery::mock(FormInterface::class);
        $this->form = Mockery::mock(FormInterface::class);

        $cache = new ArrayAdapter();
        $this->propExtractor = Mockery::mock(PropertyInfoExtractorInterface::class);
        $this->ext = new PropertyInfoLabelFormExtension($this->propExtractor, $cache);
    }

    public function testSkipIfHasLabel(): void
    {
        $this->validBuildLabel('blah', 'blah');
    }

    public function testSkipIfLabelIsFalse(): void
    {
        $this->validBuildLabel(false, false);
    }

    public function testSkipIfNoParent(): void
    {
        $this->form->expects('getParent')->andReturnNull();
        $this->validBuildLabel(null);
    }

    public function testSkipIfParentNotClass(): void
    {
        $this->form->expects('getParent')->andReturn($this->root);
        $this->root->expects('getData')->andReturn([]);

        $this->validBuildLabel(null);
    }

    public function test(): void
    {
        $this->form->expects('getParent')->andReturn($this->root);
        $this->form->expects('getName')->andReturn('name');
        $this->root->expects('getData')->andReturn(new TestEntity());
        $this->propExtractor->expects('getShortDescription')->with(TestEntity::class, 'name')->andReturn('My Name');

        $this->validBuildLabel('My Name');
    }

    private function validBuildLabel($exp, $existLabel = null): void
    {
        $view = new FormView();
        $view->vars['label'] = $existLabel;
        $this->ext->finishView($view, $this->form, []);
        self::assertEquals($exp, $view->vars['label'] ?? null);
    }
}
