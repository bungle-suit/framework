<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Form;

use Bungle\Framework\Entity\EntityMeta;
use Bungle\Framework\Entity\EntityMetaRepository;
use Bungle\Framework\Entity\EntityPropertyMeta;
use Bungle\Framework\Entity\EntityRegistry;
use Bungle\Framework\Form\BungleFormTypeGuesser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

final class BungleFormTypeGuesserTest extends TestCase
{
    private function createGuesser(): array
    {
        $entityRegistry = $this->createStub(EntityRegistry::class);
        $entityMetaRepository = $this->createStub(EntityMetaRepository::class);
        $inner = $this->createStub(FormTypeGuesserInterface::class);
        $guesser = new BungleFormTypeGuesser($inner, $entityMetaRepository, $entityRegistry);

        return [$guesser, $inner, $entityRegistry, $entityMetaRepository];
    }

    public function testGuessTypeInnerNull(): void
    {
        list($guesser) = $this->createGuesser();
        self::assertNull($guesser->guessType('Some\Entity', 'ID'));
    }

    public function testGuessTypeNoHigh(): void
    {
        list($guesser, $inner, $entityRegistry) = $this->createGuesser();
        $guess = new TypeGuess(TextType::class, [], Guess::MEDIUM_CONFIDENCE);
        $inner
        ->method('guessType')
        ->willReturn($guess);
        $entityRegistry->method('getHighSafe')->willReturn('');
        self::assertEquals($guess, $guesser->guessType('Some\Entity', 'ID'));
    }

    public function testGuessTypeSetLabel(): void
    {
        list($guesser, $inner, $entityRegistry, $entityMetaRepository) = $this->createGuesser();
        $inner
        ->method('guessType')
        ->willReturn(new TypeGuess(TextType::class, [], Guess::MEDIUM_CONFIDENCE));
        $entityRegistry->method('getHighSafe')->willReturn('ord');
        $entityMetaRepository
        ->method('get')->
        willReturn(new EntityMeta(
            'Some\Entity',
            'Wow',
            [
            new EntityPropertyMeta('id', 'No', 'int'),
            new EntityPropertyMeta('name', 'Foo', 'int'),
            ]
        ));

        self::assertEquals(
            new TypeGuess(TextType::class, ['label' => 'No'], Guess::MEDIUM_CONFIDENCE),
            $guesser->guessType('Some\Entity', 'id')
        );
    }
}
