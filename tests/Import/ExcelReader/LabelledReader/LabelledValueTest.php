<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader\LabelledReader;

use Bungle\Framework\Import\ExcelReader\LabelledReader\Context;
use Bungle\Framework\Import\ExcelReader\LabelledReader\LabelledValue;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class LabelledValueTest extends MockeryTestCase
{
    private LabelledValue $lv;
    private $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lv = new LabelledValue('prop', 'lbl');
        $this->context = Mockery::mock(Context::class);
    }

    public function testLabelMatches(): void
    {
        self::assertTrue($this->lv->labelMatches('lbl', $this->context));
        self::assertFalse($this->lv->labelMatches('lbl ', $this->context));
        self::assertFalse($this->lv->labelMatches('foo', $this->context));
    }

    public function testRead(): void
    {
        // by default use identity
        self::assertEquals('foo', $this->lv->read('foo', $this->context));

        // use custom converter
        $this->lv->setConverter(fn ($v) => intval($v));
        self::assertEquals(1, $this->lv->read('1', $this->context));
    }
}
