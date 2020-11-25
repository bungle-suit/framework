<?php

declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\LabelledReader;

use Bungle\Framework\FP;

/**
 * @phpstan-template T of object
 * @implements LabelledValueInterface<T>
 */
abstract class AbstractLabelledValue implements LabelledValueInterface
{
    private string $path;

    /** @var callable(mixed, Context<T>): mixed; */
    private $converter;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->converter = [FP::class, 'identity'];
    }

    /**
     * @phpstan-param Context<T> $context
     */
    abstract public function labelMatches(string $label, Context $context): bool;

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @phpstan-param Context<T> $context
     */
    public function read($val, Context $context)
    {
        return ($this->converter)($val, $context);
    }

    /**
     * @phpstan-param Context<T> $context
     */
    public function onSectionEnd(Context $context): void
    {
    }

    public function getConverter(): callable
    {
        return $this->converter;
    }

    /**
     * @phpstan-return self<T>
     */
    public function setConverter(callable $converter): self
    {
        $this->converter = $converter;

        return $this;
    }
}
