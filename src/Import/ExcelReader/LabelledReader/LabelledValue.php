<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\LabelledReader;

use Bungle\Framework\FP;

/**
 * @phpstan-template T of object
 */
class LabelledValue implements LabelledValueInterface
{
    private string $path;
    private string $label;

    /** @var callable(mixed, Context<T>): mixed; */
    private $converter;

    public function __construct(string $path, string $label)
    {
        $this->path = $path;
        $this->label = $label;
        $this->converter = [FP::class, 'identity'];
    }

    /**
     * @phpstan-param Context<T> $context
     */
    public function labelMatches(string $label, Context $context): bool
    {
        return $this->label === $label;
    }

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
    public function onSectionEnd(Context $context)
    {
    }

    public function getConverter(): callable
    {
        return $this->converter;
    }

    public function setConverter(callable $converter): self
    {
        $this->converter = $converter;

        return $this;
    }
}
