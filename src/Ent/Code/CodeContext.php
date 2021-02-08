<?php

declare(strict_types=1);

namespace Bungle\Framework\Ent\Code;

use Bungle\Framework\Model\HasAttributes;
use Bungle\Framework\Model\HasAttributesInterface;
use Webmozart\Assert\Assert;

class CodeContext implements HasAttributesInterface
{
    use HasAttributes;

    /**
     * Stores the result code, if not set, normally the last step set $result field.
     */
    public string $result = '';

    /**
     * Stores section of code, many steps appends section, and step like join to join
     * them together.
     * @var string[]
     */
    private array $sections = [];

    /**
     * @return string[]
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    /**
     * Set section by index, index must exist, used to replace exist value with
     * regenerated value.
     */
    public function setSection(int $idx, string $val): void
    {
        $this->sections[$idx] = $val;
    }

    /** @var array<int, CarriagableCoderStepInterface<mixed>> */
    private array $carriageSteps = [];

    /**
     * Append $section to $sections
     *
     * If $ignoreEmpty, section not added if $section is empty.
     *
     * @param CarriagableCoderStepInterface<mixed> $carriageStep register carriage step if not null.
     */
    public function addSection(
        string $section,
        bool $ignoreEmpty = false,
        CarriagableCoderStepInterface $carriageStep = null
    ): void {
        if (!$ignoreEmpty || $section !== '') {
            $this->sections[] = $section;
        }

        if ($carriageStep) {
            Assert::false(
                $ignoreEmpty,
                'Must not ignore empty for '.CarriagableCoderStepInterface::class
            );
            $this->carriageSteps[count($this->sections) - 1] = $carriageStep;
        }
    }

    /**
     * @return array<int, CarriagableCoderStepInterface<mixed>>
     */
    public function getCarriageSteps(): array
    {
        return $this->carriageSteps;
    }

    /**
     * Get the code result.
     *
     * Normally the last step will set self::result field, and __toString()
     * return it as the end result.
     * But if not, __toString() join sections up by '-'.
     */
    public function __toString(): string
    {
        if ($this->result) {
            return $this->result;
        }

        return implode('-', $this->sections);
    }
}
