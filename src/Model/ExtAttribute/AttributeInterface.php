<?php
declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

use RuntimeException;

interface AttributeInterface
{
    public function getAttribute(): string;
    public function setAttribute(string $v): void;

    public function getValue(): string;
    public function setValue(string $v): void;

    /**
     * Treat the value as bool:
     *
     * 1: true
     * 0: false
     * @throws RuntimeException if other value
     */
    public function asBool(): bool;

    public function setBool(bool $v): void;

    public function asInt(): int;

    public function setInt(int $v): void;

    public function asFloat(): float;

    public function setFloat(float $v): void;

    /** @return string[] */
    public function asStringArray(): array;

    /** @param string[] $v */
    public function setStringArray(array $v): void;
}
