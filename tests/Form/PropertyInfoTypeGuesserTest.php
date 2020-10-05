<?php

/** @noinspection PhpParamsInspection */
declare(strict_types=1);

namespace Bungle\Framework\Tests\Form;

use Bungle\Framework\Form\PropertyInfoTypeGuesser;
use DateTime;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\ValueGuess;
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
        $t = new Type(Type::BUILTIN_TYPE_STRING);
        $this->propertyInfoExtractor
            ->allows('getTypes')
            ->with(self::class, 'foo')
            ->andReturn([$t]);
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
            $exp = new TypeGuess($formType, $formOptions, Guess::HIGH_CONFIDENCE);
            self::assertEquals($exp, $this->guesser->guessType(self::class, 'foo'));
        }
    }

    public function guessTypeProvider(): array
    {
        $collType = new Type(Type::BUILTIN_TYPE_INT, false, null, true);

        return [
            [new Type(Type::BUILTIN_TYPE_NULL), 'null', []],
            [new Type(Type::BUILTIN_TYPE_INT), IntegerType::class, []],
            [new Type(Type::BUILTIN_TYPE_FLOAT), NumberType::class, []],
            [new Type(Type::BUILTIN_TYPE_BOOL), CheckboxType::class, []],
            [
                new Type(Type::BUILTIN_TYPE_OBJECT, false, DateTime::class),
                DateType::class,
                [
                    'html5' => true,
                    'widget' => 'single_text',
                ],
            ],
            [$collType, 'null', []],
        ];
    }

    public function testGuessNullableNotRequired(): void
    {
        $t = new Type(Type::BUILTIN_TYPE_STRING);
        $this->propertyInfoExtractor
            ->expects('getTypes')
            ->with(self::class, 'foo')
            ->andReturn([$t]);
        self::assertNull($this->guesser->guessRequired(self::class, 'foo'));

        $t = new Type(Type::BUILTIN_TYPE_STRING, true);
        $this->propertyInfoExtractor
            ->expects('getTypes')
            ->with(self::class, 'bar')
            ->andReturn([$t]);
        $exp = new ValueGuess(false, Guess::MEDIUM_CONFIDENCE);
        self::assertEquals($exp, $this->guesser->guessRequired(self::class, 'bar'));
    }
}
