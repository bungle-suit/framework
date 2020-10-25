<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Form;

use Bungle\Framework\Form\ExtAttributeTypeGuesser;
use Bungle\Framework\Tests\Model\ExtAttribute\TestNormalizedAttributeSet;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Guess\TypeGuess;

class ExtAttributeTypeGuesserTest extends MockeryTestCase
{
    private ExtAttributeTypeGuesser $g;

    protected function setUp(): void
    {
        parent::setUp();

        $this->g = new ExtAttributeTypeGuesser();
    }

    public function testBool(): void
    {
        $guess = $this->g->guessType(TestNormalizedAttributeSet::class, 'a');

        self::assertEquals(CheckboxType::class, $guess->getType());
        self::assertEquals(TypeGuess::VERY_HIGH_CONFIDENCE, $guess->getConfidence());
        self::assertEquals(
            [
                'label' => 'Foo',
                'help' => 'Foo Desc',
                'required' => false,
            ],
            $guess->getOptions()
        );
    }

    public function testSupports(): void
    {
        self::assertNull(ExtAttributeTypeGuesser::supports('int', 'blah'));
        self::assertNotNull(
            ExtAttributeTypeGuesser::supports(TestNormalizedAttributeSet::class, 'a')
        );
        self::assertNull(
            ExtAttributeTypeGuesser::supports(TestNormalizedAttributeSet::class, 'unknown')
        );
    }
}
