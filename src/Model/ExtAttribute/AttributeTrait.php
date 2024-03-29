<?php

declare(strict_types=1);

namespace Bungle\Framework\Model\ExtAttribute;

use Doctrine\ORM\Mapping as ORM;
use RuntimeException;

/**
 * Help implement AttributeInterface
 */
trait AttributeTrait
{
    /**
     * @ORM\Id
     * @ORM\Column()
     */
    protected string $attribute;

    public function getAttribute(): string
    {
        return $this->attribute;
    }

    public function setAttribute(string $v): void
    {
        $this->attribute = $v;
    }

    /**
     * @ORM\Column()
     */
    protected string $value = '';

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $v): void
    {
        $this->value = $v;
    }

    /**
     * Treat the value as bool:
     *
     * 1: true
     * 0: false
     * @throws RuntimeException if other value
     */
    public function asBool(): bool
    {
        switch ($this->getValue()) {
            case '1':
                return true;
            case '':
                return false;
            default:
                throw new RuntimeException("{$this->getValue()} not valid bool attribute value");
        }
    }

    public function setBool(bool $v): void
    {
        $this->setValue($v ? '1' : '');
    }

    public function asInt(): int
    {
        return intval($this->value);
    }

    public function setInt(int $v): void
    {
        $this->value = $v === 0 ? '' : strval($v);
    }

    public function asFloat(): float
    {
        return floatval($this->value);
    }

    public function setFloat(float $v): void
    {
        $this->value = self::encodeFloat($v);
    }

    public static function encodeFloat(float $v): string
    {
        return $v === 0.0 ? '' : strval($v);
    }

    /** @return string[] */
    public function asStringArray(): array
    {
        $r = explode(',', trim($this->value));
        if ($r[0] === '' && count($r) === 1) {
            return [];
        }

        return $r;
    }

    /** @param string[] $v */
    public function setStringArray(array $v): void
    {
        $this->value = implode(',', $v);
    }
}
