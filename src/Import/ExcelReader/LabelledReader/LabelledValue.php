<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\LabelledReader;

use Bungle\Framework\FP;

/**
 * @phpstan-template T of object
 * @implements LabelledValueInterface<T>
 */
class LabelledValue implements LabelledValueInterface
{
    private string $path;
    /** @var string[] */
    private array $labels;

    /** @var callable(mixed, Context<T>): mixed; */
    private $converter;

    public function __construct(string $path, string ...$labels)
    {
        $this->path = $path;
        $this->labels = $labels;
        $this->converter = [FP::class, 'identity'];
    }

    /**
     * @phpstan-param Context<T> $context
     */
    public function labelMatches(string $label, Context $context): bool
    {
        return in_array($label, $this->labels);
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
