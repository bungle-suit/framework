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
    public array $sections = [];

    /**
     * Append $section to $sections
     */
    public function addSection(string $section): void
    {
        $this->sections[] = $section;
    }
}
