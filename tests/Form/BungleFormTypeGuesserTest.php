<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Form;

use Bungle\Framework\Entity\EntityMeta;
use Bungle\Framework\Entity\EntityPropertyMeta;
use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\Form\BungleFormTypeGuesser;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\ValueGuess;

final class BungleFormTypeGuesserTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @var EntityRegistry|MockInterface */
    private $entityRegistry;
    /** @var MockInterface|FormTypeGuesserInterface */
    private $inner;
    private BungleFormTypeGuesser $guesser;

    public function setUp(): void
    {
        $this->entityRegistry = Mockery::mock(EntityRegistry::class);
        $this->inner = Mockery::mock(FormTypeGuesserInterface::class);
        $this->guesser = new BungleFormTypeGuesser($this->inner, $this->entityRegistry);
    }

    public function testGuessTypeInnerNull(): void
    {
        $this->inner->allows('guessType')->andReturn(null);
        self::assertNull($this->guesser->guessType('Some\Entity', 'ID'));
    }

    public function testGuessTypeSetLabel(): void
    {
        $this
            ->inner
            ->allows('guessType')
            ->andReturn(new TypeGuess(ColorType::class, [], Guess::MEDIUM_CONFIDENCE));
        $this->entityRegistry
            ->allows('getEntityMeta')
            ->andReturn(new EntityMeta(
                'Some\Entity',
                'Wow',
                [
                    new EntityPropertyMeta('id', 'No'),
                    new EntityPropertyMeta('name', 'Foo'),
                ]
            ));

        self::assertEquals(
            new TypeGuess(ColorType::class, ['label' => 'No'], Guess::MEDIUM_CONFIDENCE),
            $this->guesser->guessType('Some\Entity', 'id')
        );
    }

    public function testSetTextTypeEmptyData(): void
    {
        $this->inner
            ->allows('guessType')
            ->andReturn(new TypeGuess(TextType::class, [], Guess::MEDIUM_CONFIDENCE));
        $this->entityRegistry
            ->allows('getEntityMeta')
            ->andReturn(new EntityMeta(
                'Some\Entity',
                'Wow',
                [
                    new EntityPropertyMeta('id', 'No'),
                    new EntityPropertyMeta('name', 'Foo'),
                ]
            ));
        self::assertEquals(
            new TypeGuess(TextType::class, [
                'label' => 'Foo',
                'empty_data' => '',
            ], Guess::MEDIUM_CONFIDENCE),
            $this->guesser->guessType('Some\Entity', 'name')
        );
    }

    public function testDateTimeField(): void
    {
        $this->inner
            ->allows('guessType')
            ->andReturn(new TypeGuess(DateTimeType::class, [], Guess::MEDIUM_CONFIDENCE));
        $this->entityRegistry
            ->allows('getEntityMeta')
            ->andReturn(new EntityMeta(
                'Some\Entity',
                'Wow',
                [new EntityPropertyMeta('name', 'No')]
            ));
        self::assertEquals(
            new TypeGuess(DateTimeType::class, [
                'label' => 'No',
                'widget' => 'single_text',
            ], Guess::MEDIUM_CONFIDENCE),
            $this->guesser->guessType('Some\Entity', 'name')
        );
    }

    public function testGuessRequired(): void
    {
        $expGuess = new ValueGuess(true, Guess::VERY_HIGH_CONFIDENCE);
        $this->inner->allows('guessRequired')->andReturn($expGuess);
        self::assertSame($expGuess, $this->guesser->guessRequired('Some\Entity', 'name'));
    }

    public function testGuessMaxLength(): void
    {
        $expGuess = new ValueGuess(true, Guess::VERY_HIGH_CONFIDENCE);
        $this->inner->allows('guessMaxLength')->andReturn($expGuess);
        self::assertSame($expGuess, $this->guesser->guessMaxLength('Some\Entity', 'name'));
    }

    public function testGuessPattern(): void
    {
        $expGuess = new ValueGuess(true, Guess::VERY_HIGH_CONFIDENCE);
        $this->inner->allows('guessPattern')->andReturn($expGuess);
        self::assertSame($expGuess, $this->guesser->guessPattern('Some\Entity', 'name'));
    }
}
