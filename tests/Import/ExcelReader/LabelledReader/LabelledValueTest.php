<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Import\ExcelReader\LabelledReader;

use Bungle\Framework\Import\ExcelReader\LabelledReader\Context;
use Bungle\Framework\Import\ExcelReader\LabelledReader\LabelledValue;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class LabelledValueTest extends MockeryTestCase
{
    /** @var Context<object>|Mockery\MockInterface */
    private $context;
    /** @var LabelledValue<object> */
    private LabelledValue $lv;
    /** @var LabelledValue<object> */
    private LabelledValue $lvs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lv = new LabelledValue('prop', 'lbl');
        $this->lvs = new LabelledValue('prop', 'lbl1', 'lbl2');
        $this->context = Mockery::mock(Context::class);
    }

    public function testLabelMatches(): void
    {
        self::assertTrue($this->lv->labelMatches('lbl'));
        self::assertFalse($this->lv->labelMatches('lbl '));
        self::assertFalse($this->lv->labelMatches('foo'));

        self::assertTrue($this->lvs->labelMatches('lbl1'));
        self::assertTrue($this->lvs->labelMatches('lbl2'));
        self::assertFalse($this->lvs->labelMatches('foo'));
    }

    public function testRead(): void
    {
        // by default use identity
        self::assertEquals('foo', $this->lv->read('foo', $this->context));

        // use custom converter
        $this->lv->setConverter(fn($v) => intval($v));
        self::assertEquals(1, $this->lv->read('1', $this->context));
    }
}
