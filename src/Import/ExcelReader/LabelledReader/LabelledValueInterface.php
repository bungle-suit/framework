<?php
declare(strict_types=1);

namespace Bungle\Framework\Import\ExcelReader\LabelledReader;

/**
 * interface to parse a value of @see LabelledReader
 * @phpstan-template T
 */
interface LabelledValueInterface
{
    /**
     * Returns true if the label matches.
     * @phpstan-param Context<T> $context
     */
    public function labelMatches(string $label, Context $context): bool;

    /**
     * Return object path where the value will assign to.
     */
    public function getPath(): string;

    /**
     * @param mixed $val
     * @phpstan-param Context<T> $context
     * @return mixed
     */
    public function read($val, Context $context);

    /**
     * Called on section end, useful such as require post-process.
     * @phpstan-param Context<T> $context
     */
    public function onSectionEnd(Context $context): void;
}
