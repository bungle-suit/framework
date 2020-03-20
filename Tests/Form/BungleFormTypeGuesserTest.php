<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Form;

use Bungle\Framework\Entity\EntityMeta;
use Bungle\Framework\Entity\EntityMetaRepository;
use Bungle\Framework\Entity\EntityPropertyMeta;
use Bungle\Framework\Form\BungleFormTypeGuesser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

final class BungleFormTypeGuesserTest extends TestCase
{
    private function createGuesser(): array
    {
        $entityMetaRepository = $this->createStub(EntityMetaRepository::class);
        $inner = $this->createStub(FormTypeGuesserInterface::class);
        $guesser = new BungleFormTypeGuesser($inner, $entityMetaRepository);

        return [$guesser, $inner, $entityMetaRepository];
    }

    public function testGuessTypeInnerNull(): void
    {
        list($guesser) = $this->createGuesser();
        self::assertNull($guesser->guessType('Some\Entity', 'ID'));
    }

    public function testGuessTypeSetLabel(): void
    {
        list($guesser, $inner, $entityMetaRepository) = $this->createGuesser();
        $inner
          ->method('guessType')
          ->willReturn(new TypeGuess(ColorType::class, [], Guess::MEDIUM_CONFIDENCE));
        $entityMetaRepository
          ->method('get')
          ->willReturn(new EntityMeta(
              'Some\Entity',
              'Wow',
              [
                new EntityPropertyMeta('id', 'No', 'int'),
                new EntityPropertyMeta('name', 'Foo', 'int'),
              ]
          ));

        self::assertEquals(
            new TypeGuess(ColorType::class, ['label' => 'No'], Guess::MEDIUM_CONFIDENCE),
            $guesser->guessType('Some\Entity', 'id')
        );
    }

    public function testSetTextTypeEmptyData(): void
    {
        list($guesser, $inner, $entityMetaRepository) = $this->createGuesser();
        $inner
          ->method('guessType')
          ->willReturn(new TypeGuess(TextType::class, [], Guess::MEDIUM_CONFIDENCE));
        $entityMetaRepository
          ->method('get')
          ->willReturn(new EntityMeta(
              'Some\Entity',
              'Wow',
              [
                new EntityPropertyMeta('id', 'No', 'int'),
                new EntityPropertyMeta('name', 'Foo', 'int'),
              ]
          ));
        self::assertEquals(
            new TypeGuess(TextType::class, [
              'label' => 'Foo',
              'empty_data' => '',
            ], Guess::MEDIUM_CONFIDENCE),
            $guesser->guessType('Some\Entity', 'name')
        );
    }

    public function testDateTimeField(): void
    {
        list($guesser, $inner, $entityMetaRepository) = $this->createGuesser();
        $inner
          ->method('guessType')
          ->willReturn(new TypeGuess(DateTimeType::class, [], Guess::MEDIUM_CONFIDENCE));
        $entityMetaRepository
          ->method('get')
          ->willReturn(new EntityMeta(
              'Some\Entity',
              'Wow',
              [new EntityPropertyMeta('name', 'No')]
          ));
        self::assertEquals(
            new TypeGuess(DateTimeType::class, [
              'label' => 'No',
              'widget' => 'single_text',
            ], Guess::MEDIUM_CONFIDENCE),
            $guesser->guessType('Some\Entity', 'name')
        );
    }
}
