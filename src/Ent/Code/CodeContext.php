<?php
declare(strict_types=1);

namespace Bungle\Framework\Ent\Code;

use Bungle\Framework\Model\HasAttributes;
use Bungle\Framework\Model\HasAttributesInterface;

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
     * Append $section to $sections
     *
     * If $ignoreEmpty, section not added if $section is empty.
     */
    public function addSection(string $section, bool $ignoreEmpty = false): void
    {
        if (!$ignoreEmpty || $section !== '') {
            $this->sections[] = $section;
        }
    }
}
