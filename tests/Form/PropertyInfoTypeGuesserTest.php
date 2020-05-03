<?php
/** @noinspection PhpParamsInspection */
declare(strict_types=1);

namespace Bungle\Framework\Tests\Form;

use Bungle\Framework\Form\PropertyInfoTypeGuesser;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

class PropertyInfoTypeGuesserTest extends MockeryTestCase
{
    /** @var Mockery\MockInterface|Mockery\LegacyMockInterface|PropertyInfoExtractorInterface */
    private $propertyInfoExtractor;
    private PropertyInfoTypeGuesser $guesser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->propertyInfoExtractor = Mockery::mock(PropertyInfoExtractorInterface::class);
        $this->guesser = new PropertyInfoTypeGuesser($this->propertyInfoExtractor);
    }

    public function testGuessValue(): void
    {
        self::assertNull($this->guesser->guessRequired(self::class, 'foo'));
        self::assertNull($this->guesser->guessMaxLength(self::class, 'foo'));
        self::assertNull($this->guesser->guessPattern(self::class, 'foo'));
    }

    public function testIgnoreGuessTypeIfFailedExtractPropertyType(): void
    {
        $this->propertyInfoExtractor->expects('getTypes')->with(self::class, 'foo')
            ->andReturn([]);
        self::assertNull($this->guesser->guessType(self::class, 'foo'));
    }

    /**
     * @dataProvider guessTypeProvider
     */
    public function testGuessType(Type $propType, string $formType, array $formOptions): void
    {
        $this->propertyInfoExtractor->expects('getTypes')->with(self::class, 'foo')
            ->andReturn([$propType]);

        if ($formType === "null") {
            self::assertNull($this->guesser->guessType(self::class, 'foo'));
        } else {
            $exp = new TypeGuess($formType, $formOptions, Guess::LOW_CONFIDENCE);
            self::assertEquals($exp, $this->guesser->guessType(self::class, 'foo'));
        }
    }

    public function guessTypeProvider(): array
    {
        return [
            [new Type(Type::BUILTIN_TYPE_NULL), 'null', []],
            [new Type(Type::BUILTIN_TYPE_INT), IntegerType::class, []],
            [new Type(Type::BUILTIN_TYPE_FLOAT), NumberType::class, []],
            [new Type(Type::BUILTIN_TYPE_STRING), TextType::class, []],
            [new Type(Type::BUILTIN_TYPE_BOOL), CheckboxType::class, []],
            [new Type(Type::BUILTIN_TYPE_BOOL, false, null, true), ChoiceType::class, []],
        ];
    }
}
