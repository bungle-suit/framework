<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Model\ExtAttribute;

use Bungle\Framework\Model\ExtAttribute\BoolAttribute;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class BoolAttributeTest extends MockeryTestCase
{
    private BoolAttribute $def;

    protected function setUp(): void
    {
        parent::setUp();

        $this->def = new BoolAttribute('foo', 'lbl');
    }

    public function testConstructor(): void
    {
        self::assertEquals('foo', $this->def->getName());
        self::assertEquals('lbl', $this->def->getLabel());
    }

    public function testBuildForm(): void
    {
        $builder = Mockery::mock(FormBuilderInterface::class);
        $builder->expects('add')->with('foo', CheckBoxType::class, ['label' => 'lbl']);
        $this->def->buildForm($builder, []);
    }

    public function testCreateDefault(): void
    {
        self::assertFalse($this->def->createDefault());
    }

    public function testRestoreValue(): void
    {
        $attr = AttributeSetTest::newAttribute('foo');
        self::assertFalse($this->def->restoreValue($attr));

        $attr->setBool(true);
        self::assertTrue($this->def->restoreValue($attr));
    }

    public function testSaveValue(): void
    {
        $attr = AttributeSetTest::newAttribute('foo');
        $this->def->saveValue($attr, true);
        self::assertTrue($attr->asBool());

        $this->def->saveValue($attr, false);
        self::assertFalse($attr->asBool());
    }
}
